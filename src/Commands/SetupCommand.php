<?php

namespace Keepsuit\Campaigns\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Keepsuit\Campaigns\Api\ZohoAccountsApi;
use Keepsuit\Campaigns\Api\ZohoRegion;
use Keepsuit\Campaigns\Models\Token;

class SetupCommand extends Command
{
    public $signature = 'campaigns:setup';

    public $description = 'Generate token for Zoho Campaigns API';

    public function handle(): int
    {
        if (Token::findRefreshToken() !== null && ! $this->components->confirm('An active token has been found. Do you want to generate a new one?')) {
            return self::FAILURE;
        }

        $clientCredentials = $this->generateZohoApiClient();

        $region = $this->setupRegion();

        $authorizationCode = $this->getAuthorizationCode();

        $this->components->info('Generating token...');

        $response = $this->getZohoApiClient()->generateAccessToken($authorizationCode);

        DB::transaction(function () use ($response) {
            Token::saveAccessToken($response['access_token'], $response['expires_in']);
            Token::saveRefreshToken($response['refresh_token']);
        });

        $this->components->info('Token generated successfully! Remember to add the following line to your .env file:');

        $this->line(sprintf('CAMPAIGNS_REGION=%s', $region));
        $this->line(sprintf('CAMPAIGNS_CLIENT_ID="%s"', $clientCredentials['client_id']));
        $this->line(sprintf('CAMPAIGNS_CLIENT_SECRET="%s"', $clientCredentials['client_secret']));

        return self::SUCCESS;
    }

    protected function getZohoApiClient(): ZohoAccountsApi
    {
        return app(ZohoAccountsApi::class);
    }

    /**
     * @return array{client_id: string, client_secret: string}
     */
    protected function generateZohoApiClient(): array
    {
        $clientId = config('campaigns.client_id');
        $clientSecret = config('campaigns.client_secret');

        if ($clientId !== null && $clientSecret !== null) {
            return [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ];
        }

        $this->components->info('Create a Zoho api client.');

        $this->components->bulletList([
            'Go to https://www.zoho.com/accounts/protocol/oauth-setup.html and follow instructions to generate a Self Client',
            'After you have generated a Self Client, paste here the Client ID and Client Secret',
        ]);

        $clientId = $this->ask('Client ID');
        config()->set('campaigns.client_id', $clientId);

        $clientSecret = $this->ask('Client Secret');
        config()->set('campaigns.client_secret', $clientSecret);

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];
    }

    protected function setupRegion(): string
    {
        $region = config('campaigns.region');

        if ($region !== null) {
            return $region;
        }

        $options = collect(ZohoRegion::cases())
            ->keyBy(fn (ZohoRegion $region) => $region->label());

        /** @var ZohoRegion $region */
        $region = $options->get(
            $this->choice('Select your region', $options->keys()->toArray())
        );

        config('campaigns.region', $region->value);

        return $region->value;
    }

    /**
     * @return mixed
     */
    protected function getAuthorizationCode(): mixed
    {
        $this->components->info('Generate Zoho api token for campaigns.');

        $this->components->bulletList([
            'Go to https://api-console.zoho.com, select the Self Client and go to "Generate Code" tab',
            'For "Scope" field enter "ZohoCampaigns.contact.ALL" or go to https://www.zoho.com/campaigns/help/developers/access-token.html and choose the scope that fits your needs',
            'For "Time Duration" select 10 minutes',
            'For "Scope Description" enter "Laravel Zoho Campaigns" or any other description you want',
            'Click "Generate Code" button, select the campaigns account you want to connect and paste here the generated code',
        ]);

        return $this->ask('Code');
    }
}
