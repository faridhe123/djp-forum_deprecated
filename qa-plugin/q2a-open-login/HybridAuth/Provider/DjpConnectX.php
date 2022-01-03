<?php

/* !
 * Hybridauth
 * https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
 *  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
 */

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * DjpConnect OAuth2 provider adapter.
 *
 * Example:
 *
 *   $config = [
 *       'callback' => Hybridauth\HttpClient\Util::getCurrentUrl(),
 *       'keys'     => [ 'id' => '', 'secret' => '' ],
 *       'scope'    => 'https://www.googleapis.com/auth/userinfo.profile',
 *
 *        // google's custom auth url params
 *       'authorize_url_parameters' => [
 *              'approval_prompt' => 'force', // to pass only when you need to acquire a new refresh token.
 *              'access_type'     => ..,      // is set to 'offline' by default
 *              'hd'              => ..,
 *              'state'           => ..,
 *              // etc.
 *       ]
 *   ];
 *
 *   $adapter = new Hybridauth\Provider\DjpConnect( $config );
 *
 *   try {
 *       $adapter->authenticate();
 *
 *       $userProfile = $adapter->getUserProfile();
 *       $tokens = $adapter->getAccessToken();
 *       $contacts = $adapter->getUserContacts(['max-results' => 75]);
 *   }
 *   catch( Exception $e ){
 *       echo $e->getMessage() ;
 *   }
 */
class DjpConnect extends OAuth2 {

    /**
     * {@inheritdoc}
     */
    public $scope = 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://www.googleapis.com/';

    /**
     * {@inheritdoc}
     */
    // protected $authorizeUrl = 'http://localhost:8081/oauth/authorize';
    protected $authorizeUrl = 'https://djpconnectsso.pajak.go.id/oauth/authorize';
    
    protected $logoutUrl = 'https://djpconnectsso.pajak.go.id/logout';
    
    
    protected $iamMethod = '';
    protected $checkTokenParameters = [];
    protected $iamParameter = [];
    
    protected $logoutMethod = '';
    protected $logoutParameter = [];
    
    var $urlLogout = "https://djpconnectsso.pajak.go.id//logout";

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://djpconnectsso.pajak.go.id/oauth/token';
    protected $checkTokenUrl = "https://djpconnectsso.pajak.go.id/oauth/check_token";
    protected $iamUrl = "https://iam.simulasikan.com/api";

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developers.google.com/identity/protocols/OAuth2';

    /**
     * {@inheritdoc}
     */
    protected function initialize() {

        parent::initialize();

        $this->AuthorizeUrlParameters += [
            'access_type' => 'offline'
        ];

        $this->tokenRefreshParameters += [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ];

        $this->iamRequestHeader = [
            // 'Authorization' => 'Bearer ' . $this->getStoredData('iamToken')
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2NDAwNzE1NTEsImV4cCI6MTY0MDA3NTE1MSwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9BRE1JTiJdLCJ1c2VybmFtZSI6ImFkbWluIiwiaWQiOiJjMmFkNDc5OS00Njc0LTRmYzEtOTcwYS05ZjgyNDc3NmFmM2EiLCJleHBpcmVkIjoxNjQwMDc1MTUxLCJwZWdhd2FpIjpudWxsfQ.TPruuFJzvg5OKx0ymnb5eabGsM7NwRbho-v7W1nkfvXo1D_M4ksW4gTDbPPRWdW0q0nn9TZVRsZwlXcQmJPDpkHb-IKSGsolT6nwF47UuqBI9E18iiNMcZ9ExabRonOuGcVNHu01cAREWdjuu2yssHEZwjSs-Xzps7MRlo1MRuLXJHSIMbGyA1PHj2FVosTfesRnXU1Ovf2ccPGlrlDGsEPR_2-WoiBNGLFk3cPm-dVUuhJYuDxn6BAovopSQOkIZaIwcNjCwtBFmIHDdUgc8115R7RYCxQFdzKn2y-wDF3J5IsEOLqENz3GfKE7iWTSp2iiMXE5EpRYebzZXWhOi-CUGxnK9vx4ui952Ct9ZV3OedF9u5HgID3xfaGtDgclJtuT9r-MclJbkjZ3ONDNw4FdDxJPCZAM-n--lwwhhT8j8egip5U5_wBBtCRwCNzXck81o4ia8HrQ3YXPxuatYvfmyV1V1GZtWLuEDRoIFs344fI7WRhTvHmA4onIczB3utucoePOgLCaT_zs3KcpQ8PI3AEao5BKHc5G1_PHpRnSTT9oOePyK_IXdGoeSqZEpWShBeCj962K4JQbKJAfLG_nfd62TQ3W9jW9gi6uwiTrQEt3zzSE4anU_G-T32l3b7-OzUHy-RMPvyCt0dy9YT1gK_szAyRwDM_yu1egj24'
        ];
    }

    /**
     * {@inheritdoc}
     *
     * See: https://developers.google.com/identity/protocols/OpenIDConnect#obtainuserinfo
     */
    public function getUserProfile() {
        var_dump("getUserProfile");
        $response = $this->getUserData();



//        $response = $this->apiRequest('oauth2/v3/userinfo');

        $data = new Data\Collection($response);

        $profile = json_decode($data->get('scalar'), TRUE);
//        var_dump($profile['pegawai']['nama']);

        if (!$data->exists('scalar')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $profile['pegawai']['nip9'];
        $userProfile->firstName = $profile['pegawai']['nama'];
//        $userProfile->lastName = $data->get('family_name');
        $userProfile->displayName = $profile['pegawai']['nama'];
//        $userProfile->photoURL = $data->get('picture');
//        $userProfile->profileURL = $data->get('profile');
        $userProfile->gender = $data->get('gender');
        $userProfile->language = $data->get('locale');
        $userProfile->email = $data->get('email');

        $userProfile->emailVerified = ($data->get('email_verified') === true || $data->get('email_verified') === 1) ? $userProfile->email : '';

        if ($this->config->get('photo_size')) {
            $userProfile->photoURL .= '?sz=' . $this->config->get('photo_size');
        }

        return $userProfile;
    }

    protected function getUserData() {

        $urlGetUser = $this->iamUrl . "/users/" . $this->getStoredData('id_user');
        var_dump("urlGetUser", $urlGetUser);

        $response = $this->httpClient->request(
                $urlGetUser, $this->iamMethod, $this->iamParameter, $this->iamRequestHeader
        );

        $this->validateApiResponse('Unable to exchange code for API access token');

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserContacts($parameters = []) {
        $parameters = ['max-results' => 500] + $parameters;

        // Google Gmail and Android contacts
        if (false !== strpos($this->scope, '/m8/feeds/') || false !== strpos($this->scope, '/auth/contacts.readonly')) {
            return $this->getGmailContacts($parameters);
        }
    }

    /**
     * Retrieve Gmail contacts
     */
    protected function getGmailContacts($parameters = []) {
        $url = 'https://www.google.com/m8/feeds/contacts/default/full?'
                . http_build_query(array_replace(['alt' => 'json', 'v' => '3.0'], (array) $parameters));

        $response = $this->apiRequest($url);

        if (!$response) {
            return [];
        }

        $contacts = [];

        if (isset($response->feed->entry)) {
            foreach ($response->feed->entry as $idx => $entry) {
                $uc = new User\Contact();

                $uc->email = isset($entry->{'gd$email'}[0]->address) ? (string) $entry->{'gd$email'}[0]->address : '';

                $uc->displayName = isset($entry->title->{'$t'}) ? (string) $entry->title->{'$t'} : '';
                $uc->identifier = ($uc->email != '') ? $uc->email : '';
                $uc->description = '';

                if (property_exists($response, 'website')) {
                    if (is_array($response->website)) {
                        foreach ($response->website as $w) {
                            if ($w->primary == true) {
                                $uc->webSiteURL = $w->value;
                            }
                        }
                    } else {
                        $uc->webSiteURL = $response->website->value;
                    }
                } else {
                    $uc->webSiteURL = '';
                }

                $contacts[] = $uc;
            }
        }

        return $contacts;
    }
    
    function logout() {
          var_dump("kucing");
          die;
           $response = $this->httpClient->request(
                $urlGetUser, $this->logoutMethod, $this->logoutParameter, $this->iamRequestHeader
        );
      
        
        return void;
    }

}
