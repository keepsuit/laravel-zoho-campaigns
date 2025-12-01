<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Keepsuit\Campaigns\Api\ZohoAccessToken;
use Keepsuit\Campaigns\Api\ZohoCampaignsApi;
use Keepsuit\Campaigns\Api\ZohoRegion;
use Keepsuit\Campaigns\Exceptions\ZohoCampaignsApiException;

beforeEach(function () {
    $accessToken = mock(ZohoAccessToken::class);
    $accessToken->shouldReceive('get')->andReturn('access-token');

    $this->campaignsApi = new ZohoCampaignsApi(ZohoRegion::Europe, $accessToken);
});

test('list subscribe', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/json/listsubscribe?*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'resfmt' => 'JSON',
                'contactinfo' => json_encode([
                    'Contact Email' => 'john@example.com',
                    'First Name' => 'John',
                    'Last Name' => 'Doe',
                ]),
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'message' => 'A confirmation email has been sent to the user.',
                'status' => 'success',
                'code' => 0,
            ]);
        },
    ]);

    expect(fn () => $this->campaignsApi->listSubscribe('list-12345', 'john@example.com', [
        'First Name' => 'John',
        'Last Name' => 'Doe',
    ]))->not->toThrow(ZohoCampaignsApiException::class);
});

test('list subscribe error', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/json/listsubscribe?*' => function () {
            return Http::response([
                'message' => 'Invalid contact email address.',
                'status' => 'error',
                'code' => '2004',
            ]);
        },
    ]);

    expect(fn () => $this->campaignsApi->listSubscribe('list-12345', 'john@example.com'))
        ->toThrow(fn (ZohoCampaignsApiException $exception) => $exception->getMessage() === 'Invalid contact email address.' && $exception->getErrorId() === '2004');
});

test('list unsubscribe', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/json/listunsubscribe?*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'resfmt' => 'JSON',
                'contactinfo' => json_encode([
                    'Contact Email' => 'john@example.com',
                ]),
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'message' => 'User successfully unsubscribed.',
                'status' => 'success',
                'code' => 0,
            ]);
        },
    ]);

    expect(fn () => $this->campaignsApi->listUnsubscribe('list-12345', 'john@example.com'))
        ->not->toThrow(ZohoCampaignsApiException::class);
});

test('list unsubscribe error', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/json/listunsubscribe?*' => function () {
            return Http::response([
                'message' => 'Please retry after sometime.',
                'status' => 'error',
                'code' => '2101',
            ]);
        },
    ]);

    expect(fn () => $this->campaignsApi->listUnsubscribe('list-12345', 'john@example.com'))
        ->toThrow(fn (ZohoCampaignsApiException $exception) => $exception->getMessage() === 'Please retry after sometime.' && $exception->getErrorId() === '2101');
});

test('get list subscribers', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/getlistsubscribers?*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'resfmt' => 'JSON',
                'listkey' => 'list-12345',
                'fromindex' => '1',
                'range' => '20',
                'sort' => 'asc',
                'status' => 'active',
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'status' => 'success',
                'code' => 0,
                'list_of_details' => [
                    [
                        'firstname' => 'First name',
                        'lastname' => 'Last name',
                        'companyname' => 'Company name',
                        'added_time' => '01/09/2022',
                        'phone' => '123456789',
                        'contact_email' => 'test@example.com',
                        'zuid' => '12345',
                    ],
                ],
            ]);
        },
    ]);

    $response = $this->campaignsApi->listSubscribers('list-12345');

    expect($response)
        ->toBeArray()
        ->toMatchArray([
            [
                'firstname' => 'First name',
                'lastname' => 'Last name',
                'companyname' => 'Company name',
                'added_time' => '01/09/2022',
                'phone' => '123456789',
                'contact_email' => 'test@example.com',
                'zuid' => '12345',
            ],
        ]);
});

test('get list subscribers error', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/getlistsubscribers?*' => function () {
            return Http::response([
                'message' => 'Listkey is empty or invalid.',
                'status' => 'error',
                'code' => '2501',
            ]);
        },
    ]);

    expect(fn () => $this->campaignsApi->listSubscribers('list-12345', 'john@example.com'))
        ->toThrow(fn (ZohoCampaignsApiException $exception) => $exception->getMessage() === 'Listkey is empty or invalid.' && $exception->getCode() === 2501);
});

test('get list subscribers count', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/listsubscriberscount?*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'resfmt' => 'JSON',
                'listkey' => 'list-12345',
                'status' => 'active',
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'status' => 'success',
                'code' => 0,
                'no_of_contacts' => 2,
            ]);
        },
    ]);

    $response = $this->campaignsApi->listSubscribersCount('list-12345');

    expect($response)
        ->toBe(2);
});

test('get list subscribers count error', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/listsubscriberscount?*' => function () {
            return Http::response([
                'message' => 'Listkey is empty or invalid.',
                'status' => 'error',
                'code' => '2202',
            ]);
        },
    ]);

    expect(fn () => $this->campaignsApi->listSubscribersCount('list-12345', 'john@example.com'))
        ->toThrow(fn (ZohoCampaignsApiException $exception) => $exception->getMessage() === 'Listkey is empty or invalid.' && $exception->getCode() === 2202);

});

test('create tag', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/tag/add*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'tagName' => 'TEST',
                'color' => '#FF0000',
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'message' => 'Tag associated successfully',
                'status' => 'success',
                'code' => 0,
            ]);
        },
    ]);

    expect(fn () => $this->campaignsApi->tagCreate('TEST', [
        'color' => '#FF0000',
    ]))->not->toThrow(ZohoCampaignsApiException::class);
});

test('delete tag', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/tag/delete*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'tagName' => 'TEST',
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'message' => 'Tag deleted successfully',
                'status' => 'success',
                'code' => 0,
            ]);
        },
    ]);

    expect(fn () => $this->campaignsApi->tagDelete('TEST'))->not->toThrow(ZohoCampaignsApiException::class);
});

test('get tags', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/tag/getalltags*' => function (Request $request) {
            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'tags' => [
                    [
                        '16492000023685218' => [
                            'tagowner' => 'Zylker',
                            'tag_created_time' => '07 Feb 2019, 12:33 PM',
                            'tag_name' => 'SATLIV',
                            'tag_color' => '#48b9d1',
                            'tag_desc' => 'Test Tag',
                            'tagged_contact_count' => '64',
                            'is_crm_tag' => 'false',
                            'zuid' => 'xxxxxxxx',
                        ],
                    ],
                ],
            ]);
        },
    ]);

    $response = $this->campaignsApi->tags();

    expect($response)
        ->toBe([
            [
                'tagowner' => 'Zylker',
                'tag_created_time' => '07 Feb 2019, 12:33 PM',
                'tag_name' => 'SATLIV',
                'tag_color' => '#48b9d1',
                'tag_desc' => 'Test Tag',
                'tagged_contact_count' => '64',
                'is_crm_tag' => 'false',
                'zuid' => 'xxxxxxxx',
            ],
        ]);
});

test('associate tag', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/tag/associate*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'tagName' => 'TEST',
                'lead_email' => 'test@example.com',
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'message' => 'Tag associated successfully',
                'status' => 'success',
                'code' => 0,
            ]);
        },
    ]);

    expect(fn () => $this->campaignsApi->tagAssociate('TEST', 'test@example.com'))->not->toThrow(ZohoCampaignsApiException::class);
});

test('deassociate tag', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/tag/deassociate*' => function (Request $request) {
            parse_str($request->toPsrRequest()->getUri()->getQuery(), $query);

            expect($query)->toMatchArray([
                'tagName' => 'TEST',
                'lead_email' => 'test@example.com',
            ]);

            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'message' => 'Tag deassociated successfully',
                'status' => 'success',
                'code' => 0,
            ]);
        },
    ]);

    expect(fn () => $this->campaignsApi->tagDeassociate('TEST', 'test@example.com'))->not->toThrow(ZohoCampaignsApiException::class);
});

test('contact fields', function () {
    Http::fake([
        'campaigns.zoho.eu/api/v1.1/contact/allfields*' => function (Request $request) {
            expect($request->header('Authorization'))->toBe(['Zoho-oauthtoken access-token']);

            return Http::response([
                'response' => [
                    'fieldnames' => [
                        'fieldname' => [
                            [
                                'DISPLAY_NAME' => 'Contact Email',
                                'FIELD_NAME' => 'contact_email',
                                'IS_MANDATORY' => true,
                                'FIELD_ID' => 1127772000000000021,
                            ],
                            [
                                'DISPLAY_NAME' => 'First Name',
                                'FIELD_NAME' => 'firstname',
                                'IS_MANDATORY' => false,
                                'FIELD_ID' => 1127772000000000023,
                            ],
                        ],
                    ],
                ],
            ]);
        },
    ]);

    $fields = $this->campaignsApi->contactFields();

    expect($fields)
        ->toHaveCount(2)
        ->{0}->toBe([
            'DISPLAY_NAME' => 'Contact Email',
            'FIELD_NAME' => 'contact_email',
            'IS_MANDATORY' => true,
            'FIELD_ID' => 1127772000000000021,
        ]);
});
