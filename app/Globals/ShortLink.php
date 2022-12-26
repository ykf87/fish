<?php
namespace App\Globals;
use GuzzleHttp\Client;

class ShortLink{
	public static function get(string $url){
		$client 	= new Client();
		$request 	= $client->post(env('SHORT_API_URL'), [
			'form_params' => [
				'url' => $url,
			],
			'headers'	=> [
				'token'	=> env('SHORT_API_TOKEN'),
			]
		]);
		$response 	= (string)$request->getBody();
		$response 	= json_decode($response, TRUE);
		if(isset($response['code']) && $response['code'] == 200){
			return $response['data']['url'];
		}
		return false;
	}
}