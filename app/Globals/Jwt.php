<?php
namespace App\Globals;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token\Plain;
use App\Globals\Ens;

class Jwt{
    private static $secret      = 'Le$Sshidy!sV$IUfMF4Z@0zwdzcJ9y4KIO28oBwBkTPcZxO^E7uqB39nbOx&X!ucww';
    private static $auth        = 'N7OxtkSpdplgoroM6KHvcyU+cBGq8kZeG7V/fN3kUm8=';
    private static $type        = 'Bearer ';
    private static $configObj   = null;
    /**
     * 配置
     */
    private static function conf(){
        // $configuration = Configuration::forSymmetricSigner(
        //     new Sha256(),
        //     // replace the value below with a key of your own!
        //     InMemory::base64Encoded('mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw=')
        //     // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        // );
        if(self::$configObj == null){
            $key = InMemory::plainText(self::$secret);
            self::$configObj = Configuration::forSymmetricSigner(
                new Sha256(),
                $key
            );
        }
        return self::$configObj;
    }

    /**
     * 加密
     */
    public static function encrypt($id, $time){

    }

    /**
     * 解密
     */
    public static function decrypt($token){

    }

    /**
     * 生成token
     */
    public static function token(array $user, $ttl = '+2 year'){
        $config         = self::conf();
        $now            = new \DateTimeImmutable();
        $token          = $config->builder()
                            ->issuedBy(env('APP_NAME'))
                            ->permittedFor(env('APP_URL'))
                            ->issuedAt($now)
                            ->canOnlyBeUsedAfter($now->modify($ttl))//'+365 day'
                            ->withHeader('Author', Ens::decrypt(self::$auth));
        foreach($user as $k => $v){
            $token      = $token->withClaim($k, $v);
        }
        $token      = $token->getToken($config->signer(), $config->signingKey());
        return self::$type . $token->toString();
    }

    /**
     * 解密token
     */
    public static function untoken($token){
        $config             = self::conf();
        if(!$token){
            return false;
        }
        try{
            $token      = str_replace(self::$type, '', $token);
            if($token && substr_count($token, '.') >= 2){
                $token      = $config->parser()->parse($token);
                assert($token instanceof Plain);
                return $token;
            }
        }catch (\InvalidArgumentException $e){
            return false;
        }
        return false;
    }
}
