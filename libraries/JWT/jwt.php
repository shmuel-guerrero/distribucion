<?php
    require libraries.'/JWT/vendor/autoload.php';
    use Firebase\JWT\JWT;
    class Auth{
        private static $semilla = semilla;
        private static $encrypt = ['HS256'];
        private static $aud = null;
        public static function SignIn($Datos){
            $Tiempo = time();
            $Token = [
                'exp' => $Tiempo + (60*60)*24,
                'aud' => self::Aud(),
                'data' => $Datos
            ];
            return JWT::encode($Token, self::$semilla);
        }
        public static function Check($Token){
            if(empty($Token)):
                return false;
            endif;
            $decode = JWT::decode(
                $Token,
                self::$semilla,
                self::$encrypt
            );
            if(!$decode):
                return false;
            endif;
            return true;
        }
        public static function GetData($Token){
            return JWT::decode(
                $Token,
                self::$semilla,
                self::$encrypt
            )->data;
        }
        private static function Aud(){
            $aud = '';
            if (!empty($_SERVER['HTTP_CLIENT_IP']))
                $aud = $_SERVER['HTTP_CLIENT_IP'];
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
                $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
            else
                $aud = $_SERVER['REMOTE_ADDR'];
            $aud.=@$_SERVER['HTTP_USER_AGENT'];
            $aud.=gethostname();
            return sha1($aud);
        }
    }
    /*
    require(libraries.'/JWT/jwt.php');
    $auth=new Auth;
    //Crear
    $Crear=$auth->SignIn(['Nombre'=>'Juan Perez Gomez','Id'=>1]);
    echo json_encode($Crear);
    echo "<br>";
    //Recuperar
    $Recuperado=$auth->GetData($Crear);
    echo json_encode($Recuperado);
    echo "<br>";
    //Verificar
    $Verificar=$auth->Check($Crear);
    if($Verificar):
        echo 'Paso';
    else:
        echo 'No Paso';
    endif;
    */