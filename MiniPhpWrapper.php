<?php

/**
 * Created by IntelliJ IDEA.
 * User: GirayBAL (giraybal@gmail.com)
 * Date: 11.06.2016
 * Time: 14:49
 *
 * Last edit: Date: 05.07.2018
 */

namespace MPW {

    const SESSION_CACHE_COUNTRY = 'SESSION_CACHE_COUNTRY';

    use CL;
    use Data;
    use DateTime;
    use PDO;
    use PDOException;
//    use PHPMailer;
    use ReflectionClass;

    class MPW {
        public $LK;
        public $GL;
        public $CL;
        public $DB;
        public $LG;
        public $GA;

        public function __construct() {
            require_once('Data.php');

            Data::$DEBUG_MODE = gethostname() == ('DESKTOP-123');

            require_once('CL.php');

            session_start();// Session start for general use.

            // Logs
            ini_set('error_reporting', Data::DISPLAY_ERRORS ? E_ALL : 0);
            ini_set('display_errors', Data::DISPLAY_ERRORS ? 'On' : 'Off');
            ini_set('log_errors', Data::LOG_ERRORS ? 'On' : 'Off');

            //
            $this->LK = new LK();
            $this->GL = new GL();
            $this->CL = new CL();
            $this->LG = new LG();
            $this->GA = new GA();

            // Daos
            foreach (Data::$DAOS as $dao) require_once('daos/' . $dao);

            $host = Data::$DEBUG_MODE ? Data::DATABASE_HOST_DEBUG : Data::DATABASE_HOST;
            $name = Data::$DEBUG_MODE ? Data::DATABASE_NAME_DEBUG : Data::DATABASE_NAME;
            $username = Data::$DEBUG_MODE ? Data::DATABASE_USERNAME_DEBUG : Data::DATABASE_USERNAME;
            $password = Data::$DEBUG_MODE ? Data::DATABASE_PASSWORD_DEBUG : Data::DATABASE_PASSWORD;

            $this->DB = new DB($host, $name, $username, $password);
        }
    }

    class LK {
        public $PATH_ROOT;
        public $PATH_APP;
        public $PATH_PANEL;
        public $PATH_MEDIA;

        public $FILE_HEADER;
        public $FILE_HEADER_PANEL;
        public $FILE_FOOTER;
        public $FILE_FOOTER_PANEL;

        public $URL_ROOT;
        public $URL_STATIC;
        public $URL_STATIC_CSS;
        public $URL_STATIC_SCRIPT;
        public $URL_STATIC_SCRIPT_LOCAL;
        public $URL_STATIC_IMAGE;
        public $URL_STATIC_IMAGE_PANEL;
        public $URL_MEDIA;

        public function __construct() {
            $this->PATH_ROOT = realpath(dirname(__FILE__) . '/../') . DIRECTORY_SEPARATOR;
            $this->PATH_APP = $this->PATH_ROOT . 'app' . DIRECTORY_SEPARATOR;
            $this->PATH_PANEL = $this->PATH_ROOT . 'panel' . DIRECTORY_SEPARATOR;
            $this->PATH_MEDIA = $this->PATH_ROOT . 'media' . DIRECTORY_SEPARATOR;

            $this->FILE_HEADER = $this->PATH_ROOT . 'inc' . DIRECTORY_SEPARATOR . 'header.php';
            $this->FILE_FOOTER = $this->PATH_ROOT . 'inc' . DIRECTORY_SEPARATOR . 'footer.php';

            $this->FILE_HEADER_PANEL = $this->PATH_PANEL . 'inc' . DIRECTORY_SEPARATOR . 'header.php';
            $this->FILE_FOOTER_PANEL = $this->PATH_PANEL . 'inc' . DIRECTORY_SEPARATOR . 'footer.php';

            $this->URL_ROOT = Data::$DEBUG_MODE ? Data::URL_ROOT_DEBUG : Data::URL_ROOT;
            $this->URL_STATIC = $this->URL_ROOT . 'static/';
            $this->URL_STATIC_CSS = $this->URL_STATIC . 'css/';
            $this->URL_STATIC_SCRIPT = $this->URL_STATIC . 'script/';
            $this->URL_STATIC_SCRIPT_LOCAL = $this->URL_STATIC_SCRIPT . 'local/';
            $this->URL_STATIC_IMAGE = $this->URL_STATIC . 'image/';
            $this->URL_STATIC_IMAGE_PANEL = $this->URL_STATIC_IMAGE . 'panel/';
            $this->URL_MEDIA = $this->URL_ROOT . 'media/';
        }
    }

    /**
     * Created by IntelliJ IDEA.
     * User: Giray10
     * Date: 24.01.2016
     * Time: 15:31
     */
    class DaoProto {

        //FIXME: 'newInstanceArgs' kullanıldığından database'deki colon sırası dikkate alınarak doldurulur!

        protected static function toObject($class, $dataArr) {
            if ($dataArr === null || !is_array($dataArr)) return null;
            $reflector = new ReflectionClass($class);
            return $reflector->newInstanceArgs($dataArr);
        }

        protected static function toObjectFromPDO($class, $result) {
            if ($result === null || !is_array($result)) return null;
            if ($result[0] == 0) return false;
            return DaoProto::toObject($class, $result[1][0]);
        }

        protected static function toObjectArrFromPDO($class, $result) {
            if ($result === null || !is_array($result)) return null;

            $arr = array();
            foreach ($result[1] as $data)
                $arr[] = DaoProto::toObject($class, $data);

            return $arr;
        }

    }

    class DB {
        // Author: GirayBAL
        // Date: 23.06.2015

        private $host, $dbName, $userName, $password;

        private $pdo = null;

        public $errorMessage = '';

        public function __construct($host, $dbName, $userName, $password) {
            $this->host = $host;
            $this->dbName = $dbName;
            $this->userName = $userName;
            $this->password = $password;
        }

        public function connect() {
            if ($this->pdo != null) return false; // Already connected

            try {
                $this->pdo = new PDO(
                    "mysql:host=$this->host;dbname=$this->dbName;charset=utf8",
                    $this->userName,
                    $this->password,
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );

                // PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',

                // TODO: Bunlara gerek varmı?
                $this->pdo->exec("SET NAMES 'utf8'");
                // $this->pdo->exec("SET CHARSET 'utf8'");
                // $this->pdo->exec("SET CHARACTER 'utf8'");
            } catch (PDOException $e) {
                $this->errorMessage = $e->getMessage();
                return false;
            }

            return true;
        }

        public function query($sql, $valuesArr = null) {
            $this->errorMessage = '';

            if ($this->pdo == null) {
                $this->errorMessage = 'Not connected to DB!';
                return null;
            }

            $effectedRows = 0;
            $results = array();

            try {
                $stmt = null;
                if ($valuesArr != null) {
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute($valuesArr);
                } else {
                    $stmt = $this->pdo->query($sql);
                }

                if (preg_match('/^SELECT /', $sql)) $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $effectedRows = $stmt->rowCount();
            } catch (PDOException $e) {
                $this->errorMessage = $e->getMessage();
                return null;
            }

            return array($effectedRows, $results);
        }

        public function getPDO() {
            return $this->pdo;
        }

        public function getLastId($name = null) {
            return $this->pdo->lastInsertId($name);
        }

        public function disconnect() {
            $this->errorMessage = '';

            if ($this->pdo == null) {
                $this->errorMessage = 'Not connected to DB!';
                return false;
            }
            $this->pdo = null;

            return true;
        }

        public function __destruct() {
            $this->disconnect();
        }
    }

    class GL {
        public function go($url) {
            echo '<meta http-equiv="refresh" content="0; url=' . $url . '">';
            exit();
        }

        public function isAjaxRequest() {
            return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        }

        public function isPostRequest() {
            return strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
        }

        public function getClientIP() {
            if (getenv('HTTP_CLIENT_IP')) {
                $ipaddress = getenv('HTTP_CLIENT_IP');
            } else
                if (getenv('HTTP_X_FORWARDED_FOR')) {
                    $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
                } else
                    if (getenv('HTTP_X_FORWARDED')) {
                        $ipaddress = getenv('HTTP_X_FORWARDED');
                    } else
                        if (getenv('HTTP_FORWARDED_FOR')) {
                            $ipaddress = getenv('HTTP_FORWARDED_FOR');
                        } else
                            if (getenv('HTTP_FORWARDED')) {
                                $ipaddress = getenv('HTTP_FORWARDED');
                            } else
                                if (getenv('REMOTE_ADDR')) {
                                    $ipaddress = getenv('REMOTE_ADDR');
                                } else {
                                    $ipaddress = null;
                                }

            return $ipaddress;
        }

        public function generateRandomString($length = 5) {
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';

            for ($i = 0; $i < $length; $i++)
                $randomString .= $characters[rand(0, strlen($characters) - 1)];

            return $randomString;
        }

        public function convertDateToHumanReadable($dateStr, $showTime = true) {
            if ($dateStr == null) return null;
            return $showTime ? date('d.m.Y H:i:s', strtotime($dateStr)) : date('d.m.Y', strtotime($dateStr));
        }

        //
        public function clearData($string) {
            // TODO: is it safe? write again soon
            $string = strip_tags($string);
            $string = htmlspecialchars($string);
            $string = trim($string);
            return $string;
        }

        // GET

        /**
         * @param $name
         * @return bool|int|null GET tanımlanmamışsa null döner, tanımlanmış ama sayı değilse false döner else intval döner
         */
        public function GET_int($name) {
            if (!isset($_GET[$name])) return null;
            if (!is_numeric($_GET[$name])) return false;
            return intval($_GET[$name]);
        }

        public function GET_str($name, $length = 0) {
            if (!isset($_GET[$name])) {
                return false;
            }
            return $this->clearData($length != 0 ? substr($_GET[$name], 0, $length) : $_GET[$name]);
        }

        public function GET_str2($name, $length = null, $default = null) {
            if (!isset($_GET[$name])) return $default;
            return $this->clearData($length != null ? substr($_GET[$name], 0, $length) : $_GET[$name]);
        }

        // POST
        public function POST_int($name) {
            if (!isset($_POST[$name])) {
                return false;
            }
            if (!is_numeric($_POST[$name])) {
                return false;
            }
            return intval($_POST[$name]);
        }

        public function POST_str($name, $length = 0) {
            if (!isset($_POST[$name])) {
                return false;
            }
            return $this->clearData($length != 0 ? substr($_POST[$name], 0, $length) : $_POST[$name]);
        }

        /**
         * verilen isimdeki değeri $POST'tan alır null'sa veya trimden sonra boş ise $defaultValue döndürür
         * @param $name
         * @param null $defaultValue $name null'sa veya trimden sonra boş ise döner
         * @param bool $canEmpty gelen değer boş olabilir mi? olabilirse '' döner olamazsa $defaultValue döner
         * @return null|string
         */
        public function getPostValue($name, $defaultValue = null, $canEmpty = false) {
            if ($name == null || $name === false) return $defaultValue;
            $parameter = isset($_POST[$name]) ? trim($_POST[$name]) : $defaultValue;
            if (!$canEmpty) $parameter = $parameter == '' ? $defaultValue : $parameter;
            return $parameter;
        }

        /**
         * Verilen değerin int mi ve 0'dan büyük mü kontrolünü yapar
         * @param $var
         * @return bool
         */
        public function isId($var) {
            if ($var === null || $var === false) return false;
            return is_numeric($var) && ((string)(int)$var == $var) && $var > 0;
        }

        public function isIndex($var) {
            if ($var === null || $var === false) return false;
            return is_numeric($var) && ((string)(int)$var == $var) && $var >= 0;
        }

//		public function POST_arr($name) {
//			if (!isset($_POST[$name])) {
//				return false;
//			}
//			if (!is_array($_POST[$name])) {
//				return false;
//			}
//			if (sizeOf($_POST[$name]) == 0) {
//				return false;
//			}
//			return $_POST[$name];
//		}

        // SESSION
//		public function SESSION_str($name, $value = '') {
//			if (isset($_SESSION[$name])) {
//				if ($value != '') {
//					return $_SESSION[$name] == $value;
//				}
//
//				return true;
//			}
//
//			return false;
//		}

        //
        public function youtube_VideoExist($videoId) {
            $url = "http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=$videoId&format=json";
            $headers = get_headers($url);

            return substr($headers[0], 9, 3) !== '404';
        }

        //
//		public function HTML_optionDays($selected = null) {
//			$result = '';
//
//			for ($i = 1; $i <= 31; $i++) {
//				$string = $i < 10 ? ('0' . $i) : $i;
//				$result .= '<option value="' . $string . '"' . ($selected != null && $selected == $string ? ' selected' : '') . '>' . $string . '</option>';
//			}
//
//			return $result;
//		}

//		public function HTML_optionMonths($selected = null) {
//			$result = '';
//
//			$monthsArr = array('Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık');
//
//			for ($i = 1; $i <= sizeOf($monthsArr); $i++) {
//				$value = $i < 10 ? ('0' . $i) : $i;
//				$result .= '<option value="' . $value . '"' . ($selected != null && $selected == $value ? ' selected' : '') . '>' . $monthsArr[$i - 1] . '</option>';
//			}
//
//			return $result;
//		}

//		public function HTML_optionYears($from, $limit = 5, $selected = null) {
//			$result = '';
//
//			for ($i = $from; $i <= ($from + $limit); $i++) {
//				$result .= '<option value="' . $i . '"' . ($selected != null && $selected == $i ? ' selected' : '') . '>' . $i . '</option>';
//			}
//
//			return $result;
//		}

        public function setPanelLoginUser($userId) {
            $_SESSION['panel_loggedInUserId'] = $userId;
        }

        public function getPanelLoginUserId() {
            return isset($_SESSION['panel_loggedInUserId']) ? $_SESSION['panel_loggedInUserId'] : null;
        }

        public function isPanelLoggedIn() {
            return isset($_SESSION['panel_loggedInUserId']);
        }

        public function panelLogOut() {
            if (isset($_SESSION['panel_loggedInUserId'])) {
                unset($_SESSION['panel_loggedInUserId']);
                return true;
            }
            return false;
        }

        public function setLoginUser($userId) {
            $_SESSION['site_loggedInUserId'] = $userId;
        }

        public function getLoginUserId() {
            return isset($_SESSION['site_loggedInUserId']) ? $_SESSION['site_loggedInUserId'] : null;
        }

        public function isSiteLoggedIn() {
            return isset($_SESSION['site_loggedInUserId']);
        }

        public function siteLogOut() {
            if (isset($_SESSION['site_loggedInUserId'])) {
                unset($_SESSION['site_loggedInUserId']);
                return true;
            }

            return false;
        }

        public function generatePasswordHash($password) {
            return md5(Data::PASSWORD_HASH . $password);
        }

        /**
         * @param $host
         * @param $port
         * @param $fromEmail
         * @param $fromName
         * @param $password
         * @param $subject
         * @param $htmlMessage
         * @param $toArr array Mail atılacak emailler array('mail@mail.com',...)
         * @param $replyToArr null|array ReplyTo ekler null or array like array('Ad Soyad'=>'mail@mail.com',...)
         * @param $bccArr null|array BCC ekler null or array like array('Ad Soyad'=>'mail@mail.com',...)
         * @return bool
         */
//        public function sendMail($host,
//                                 $port,
//                                 $fromEmail,
//                                 $fromName,
//                                 $password,
//                                 $subject,
//                                 $htmlMessage,
//                                 $toArr,
//                                 $replyToArr,
//                                 $bccArr) {
//
//            require_once('lib/PHPMailer/class.smtp.php');
//            require_once('lib/PHPMailer/class.phpmailer.php');
//
//            date_default_timezone_set('Etc/UTC');
//
//            //
//            $mail = new PHPMailer();
//
//            $mail->isSMTP();
//            //Enable SMTP debugging
//            // 0 = off (for production use)
//            // 1 = client messages
//            // 2 = client and server messages
////            $mail->SMTPDebug = 3;
////            $mail->Debugoutput = 'html';
////            $mail->Sender = 'admin@yourdomain.com';//bu ne?
//
//            $mail->Host = $host;
//            $mail->Port = $port;
//            $mail->SMTPAuth = true;
//            $mail->Username = $fromEmail;
//            $mail->Password = $password;
//            $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
//
//            $mail->CharSet = 'utf-8';
//
////            $mail->SetFrom('name@yourdomain.com', 'First Last', FALSE);
////            $mail->From = 'test@test.com';
//            $mail->FromName = $fromName;
//
//            // Can add multiply and Name is optional
//            foreach ($toArr as $toEmail) $mail->addAddress($toEmail);
//
//            if ($replyToArr != null) foreach ($replyToArr as $name => $email) $mail->addReplyTo($email, $name);
//
//            if ($bccArr != null) foreach ($bccArr as $name => $email) $mail->addBCC($email, $name);
//
//            // $mail->addCC('cc@example.com');
//            // $mail->addBCC('bcc@example.com');
//
//            // $mail->addAttachment('/var/tmp/file.tar.gz'); // Add attachments
//            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg'); // Optional name
//            $mail->isHTML(true); // Set email format to HTML
//
//            $mail->Subject = $subject;
//            $mail->Body = $htmlMessage;
//            $mail->AltBody = $htmlMessage;
//
//            if (!$mail->send()) {
//                // echo 'Message could not be sent.';
//                // echo 'Mailer Error: ' . $mail->ErrorInfo;
//
//                return false;
//            } else {
//                // echo 'Message has been sent';
//                return true;
//            }
//        }

//		public function sendMailToAdmin($subject, $htmlMessage) {
//			return $this->sendMail($subject, $htmlMessage, array(
//				Data::MAIL_ADMIN_EMAIL
//			));
//		}

        public function JSON_UNESCAPED_UNICODE($string) {
            return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($matches) {
                $sym = mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16');
                return $sym;
            }, $string);
        }

        // Validations
        public function validateTC($tc) {
            if (!is_numeric($tc)) {
                return false;
            }
            if (!preg_match("/^[0-9]{11}$/", $tc)) {
                return false;
            }

            $pr1 = intval(substr($tc, 0, 1));
            $pr2 = intval(substr($tc, 1, 1));
            $pr3 = intval(substr($tc, 2, 1));
            $pr4 = intval(substr($tc, 3, 1));
            $pr5 = intval(substr($tc, 4, 1));
            $pr6 = intval(substr($tc, 5, 1));
            $pr7 = intval(substr($tc, 6, 1));
            $pr8 = intval(substr($tc, 7, 1));
            $pr9 = intval(substr($tc, 8, 1));
            $pr10 = intval(substr($tc, 9, 1));
            $pr11 = intval(substr($tc, 10, 1));

            if ((($pr1 + $pr3 + $pr5 + $pr7 + $pr9) * 7 - ($pr2 + $pr4 + $pr6 + $pr8)) % 10 != $pr10) return false;
            if (($pr1 + $pr3 + $pr5 + $pr7 + $pr9 + $pr2 + $pr4 + $pr6 + $pr8 + $pr10) % 10 != $pr11) return false;

            return true;
        }

        public function GET_validateDate($name = 'date') {
            if (!isset($_GET[$name])) return null;
            return $this->validateDate($_GET[$name]) ? $_GET[$name] : null;
        }

        public function validateDate($date, $format = 'Y-m-d') {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }

        public function validateEmail($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }

        //Social

        /**
         * Facebook login olduktan sonra server tarafında accessToken doğrulamak için kullanılır
         *
         * @param $id string authResponse içinde gelen facebook userID
         * @param $accessToken string authResponse içinde gelen accessToken
         * @return mixed|null
         */
        public function checkFacebookAccessToken($id, $accessToken) {
            $content = @file_get_contents('https://graph.facebook.com/me?access_token=' . $accessToken);

            if ($content == null || !$content) return null;

            $result = json_decode($content, true);

            if ($result == null || !$result) return null;

            //name
            //id

            if ($result['id'] != $id) return null;

            return $result;
        }

        /**
         * //https://developers.google.com/identity/sign-in/web/backend-auth
         * //https://console.developers.google.com/
         *
         * Google login olduktan sonra server tarafında idToken doğrulamak için kullanılır
         *
         * @param $aud string app's client Id
         * @param $idToken
         * @return mixed|null
         */
        public function checkGoogleIdToken($aud, $idToken) {
            $content = @file_get_contents('https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . $idToken);

            if ($content == null || !$content) return null;

            $result = json_decode($content, true);

            if ($result == null || !$result) return null;

            //$result dizisi içeriği
            //sub: user's unique Google ID
            //name
            //email
            //aud

            if ($result['aud'] != $aud) return null;

            return $result;
        }

        public function getUserCountryCodeFromIP($isCached = false) {

            if ($isCached && isset($_SESSION[SESSION_CACHE_COUNTRY])) return $_SESSION[SESSION_CACHE_COUNTRY];

            $ip = $this->getClientIP();

            if ($ip == null || !$ip) return null;

            $content = @file_get_contents('http://www.geoplugin.net/json.gp?ip=' . $ip);

            if ($content == null || !$content) return null;

            $result = json_decode($content, true);

            if ($result == null || !$result || !is_array($result)) return null;

            $countryCode = $result['geoplugin_countryCode'];

            if ($countryCode == null || !$countryCode || $countryCode == '') return null;

            $_SESSION[SESSION_CACHE_COUNTRY] = $countryCode;

            return $countryCode;
        }

        /**
         * Google ReCaptcha checkbox undan gelen değerin kontrol edilmesi için gerekli function
         * https://developers.google.com/recaptcha/docs/verify
         * @param $secretKey string reCAPTCHA secret key
         * @param $responseToken string user response token provided by reCAPTCHA
         * @return bool|null
         */
        public function checkGoogleReCaptcha($secretKey, $responseToken) {
            //http://stackoverflow.com/a/6609181/1472483
            $data = array(
                'secret' => $secretKey,
                'response' => $responseToken,
                'remoteip' => ''
            );

            // use key 'http' even if you send the request to https://...
            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context = stream_context_create($options);
            $content = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);

//            var_dump($content);

            if ($content == null || !$content) return null;

            $result = json_decode($content, true);

            if ($result == null || !$result) return null;

            if ($result['success'] != 'true') return null;

            return true;
        }

        /**
         * 2 HARF li kod döndürür tanımlanmamışsa null döndürür
         * @return bool|null|string
         */
        public function getBrowserLanguage() {
            return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
        }

        public function isISO2CodeValid($iso2) {
            return array_key_exists($iso2, $this->countryCodesEnglish());
        }

        public function getCountryNameByISO2($iso2) {
            $countryArr = $this->countryCodesEnglish();
            if ($this->isISO2CodeValid($iso2)) return $countryArr[$iso2];
            return null;
        }

        public function countryCodesEnglish() {
            return array(
                'AF' => 'Afghanistan',
                'AX' => 'Aland Islands',
                'AL' => 'Albania',
                'DZ' => 'Algeria',
                'AS' => 'American Samoa',
                'AD' => 'Andorra',
                'AO' => 'Angola',
                'AI' => 'Anguilla',
                'AQ' => 'Antarctica',
                'AG' => 'Antigua And Barbuda',
                'AR' => 'Argentina',
                'AM' => 'Armenia',
                'AW' => 'Aruba',
                'AU' => 'Australia',
                'AT' => 'Austria',
                'AZ' => 'Azerbaijan',
                'BS' => 'Bahamas',
                'BH' => 'Bahrain',
                'BD' => 'Bangladesh',
                'BB' => 'Barbados',
                'BY' => 'Belarus',
                'BE' => 'Belgium',
                'BZ' => 'Belize',
                'BJ' => 'Benin',
                'BM' => 'Bermuda',
                'BT' => 'Bhutan',
                'BO' => 'Bolivia',
                'BA' => 'Bosnia And Herzegovina',
                'BW' => 'Botswana',
                'BV' => 'Bouvet Island',
                'BR' => 'Brazil',
                'IO' => 'British Indian Ocean Territory',
                'BN' => 'Brunei Darussalam',
                'BG' => 'Bulgaria',
                'BF' => 'Burkina Faso',
                'BI' => 'Burundi',
                'KH' => 'Cambodia',
                'CM' => 'Cameroon',
                'CA' => 'Canada',
                'CV' => 'Cape Verde',
                'KY' => 'Cayman Islands',
                'CF' => 'Central African Republic',
                'TD' => 'Chad',
                'CL' => 'Chile',
                'CN' => 'China',
                'CX' => 'Christmas Island',
                'CC' => 'Cocos (Keeling) Islands',
                'CO' => 'Colombia',
                'KM' => 'Comoros',
                'CG' => 'Congo',
                'CD' => 'Congo, Democratic Republic',
                'CK' => 'Cook Islands',
                'CR' => 'Costa Rica',
                'CI' => 'Cote D\'Ivoire',
                'HR' => 'Croatia',
                'CU' => 'Cuba',
                'CY' => 'Cyprus',
                'CZ' => 'Czech Republic',
                'DK' => 'Denmark',
                'DJ' => 'Djibouti',
                'DM' => 'Dominica',
                'DO' => 'Dominican Republic',
                'EC' => 'Ecuador',
                'EG' => 'Egypt',
                'SV' => 'El Salvador',
                'GQ' => 'Equatorial Guinea',
                'ER' => 'Eritrea',
                'EE' => 'Estonia',
                'ET' => 'Ethiopia',
                'FK' => 'Falkland Islands (Malvinas)',
                'FO' => 'Faroe Islands',
                'FJ' => 'Fiji',
                'FI' => 'Finland',
                'FR' => 'France',
                'GF' => 'French Guiana',
                'PF' => 'French Polynesia',
                'TF' => 'French Southern Territories',
                'GA' => 'Gabon',
                'GM' => 'Gambia',
                'GE' => 'Georgia',
                'DE' => 'Germany',
                'GH' => 'Ghana',
                'GI' => 'Gibraltar',
                'GR' => 'Greece',
                'GL' => 'Greenland',
                'GD' => 'Grenada',
                'GP' => 'Guadeloupe',
                'GU' => 'Guam',
                'GT' => 'Guatemala',
                'GG' => 'Guernsey',
                'GN' => 'Guinea',
                'GW' => 'Guinea-Bissau',
                'GY' => 'Guyana',
                'HT' => 'Haiti',
                'HM' => 'Heard Island & Mcdonald Islands',
                'VA' => 'Holy See (Vatican City State)',
                'HN' => 'Honduras',
                'HK' => 'Hong Kong',
                'HU' => 'Hungary',
                'IS' => 'Iceland',
                'IN' => 'India',
                'ID' => 'Indonesia',
                'IR' => 'Iran, Islamic Republic Of',
                'IQ' => 'Iraq',
                'IE' => 'Ireland',
                'IM' => 'Isle Of Man',
                'IL' => 'Israel',
                'IT' => 'Italy',
                'JM' => 'Jamaica',
                'JP' => 'Japan',
                'JE' => 'Jersey',
                'JO' => 'Jordan',
                'KZ' => 'Kazakhstan',
                'KE' => 'Kenya',
                'KI' => 'Kiribati',
                'KR' => 'Korea',
                'KW' => 'Kuwait',
                'KG' => 'Kyrgyzstan',
                'LA' => 'Lao People\'s Democratic Republic',
                'LV' => 'Latvia',
                'LB' => 'Lebanon',
                'LS' => 'Lesotho',
                'LR' => 'Liberia',
                'LY' => 'Libyan Arab Jamahiriya',
                'LI' => 'Liechtenstein',
                'LT' => 'Lithuania',
                'LU' => 'Luxembourg',
                'MO' => 'Macao',
                'MK' => 'Macedonia',
                'MG' => 'Madagascar',
                'MW' => 'Malawi',
                'MY' => 'Malaysia',
                'MV' => 'Maldives',
                'ML' => 'Mali',
                'MT' => 'Malta',
                'MH' => 'Marshall Islands',
                'MQ' => 'Martinique',
                'MR' => 'Mauritania',
                'MU' => 'Mauritius',
                'YT' => 'Mayotte',
                'MX' => 'Mexico',
                'FM' => 'Micronesia, Federated States Of',
                'MD' => 'Moldova',
                'MC' => 'Monaco',
                'MN' => 'Mongolia',
                'ME' => 'Montenegro',
                'MS' => 'Montserrat',
                'MA' => 'Morocco',
                'MZ' => 'Mozambique',
                'MM' => 'Myanmar',
                'NA' => 'Namibia',
                'NR' => 'Nauru',
                'NP' => 'Nepal',
                'NL' => 'Netherlands',
                'AN' => 'Netherlands Antilles',
                'NC' => 'New Caledonia',
                'NZ' => 'New Zealand',
                'NI' => 'Nicaragua',
                'NE' => 'Niger',
                'NG' => 'Nigeria',
                'NU' => 'Niue',
                'NF' => 'Norfolk Island',
                'MP' => 'Northern Mariana Islands',
                'NO' => 'Norway',
                'OM' => 'Oman',
                'PK' => 'Pakistan',
                'PW' => 'Palau',
                'PS' => 'Palestinian Territory, Occupied',
                'PA' => 'Panama',
                'PG' => 'Papua New Guinea',
                'PY' => 'Paraguay',
                'PE' => 'Peru',
                'PH' => 'Philippines',
                'PN' => 'Pitcairn',
                'PL' => 'Poland',
                'PT' => 'Portugal',
                'PR' => 'Puerto Rico',
                'QA' => 'Qatar',
                'RE' => 'Reunion',
                'RO' => 'Romania',
                'RU' => 'Russian Federation',
                'RW' => 'Rwanda',
                'BL' => 'Saint Barthelemy',
                'SH' => 'Saint Helena',
                'KN' => 'Saint Kitts And Nevis',
                'LC' => 'Saint Lucia',
                'MF' => 'Saint Martin',
                'PM' => 'Saint Pierre And Miquelon',
                'VC' => 'Saint Vincent And Grenadines',
                'WS' => 'Samoa',
                'SM' => 'San Marino',
                'ST' => 'Sao Tome And Principe',
                'SA' => 'Saudi Arabia',
                'SN' => 'Senegal',
                'RS' => 'Serbia',
                'SC' => 'Seychelles',
                'SL' => 'Sierra Leone',
                'SG' => 'Singapore',
                'SK' => 'Slovakia',
                'SI' => 'Slovenia',
                'SB' => 'Solomon Islands',
                'SO' => 'Somalia',
                'ZA' => 'South Africa',
                'GS' => 'South Georgia And Sandwich Isl.',
                'ES' => 'Spain',
                'LK' => 'Sri Lanka',
                'SD' => 'Sudan',
                'SR' => 'Suriname',
                'SJ' => 'Svalbard And Jan Mayen',
                'SZ' => 'Swaziland',
                'SE' => 'Sweden',
                'CH' => 'Switzerland',
                'SY' => 'Syrian Arab Republic',
                'TW' => 'Taiwan',
                'TJ' => 'Tajikistan',
                'TZ' => 'Tanzania',
                'TH' => 'Thailand',
                'TL' => 'Timor-Leste',
                'TG' => 'Togo',
                'TK' => 'Tokelau',
                'TO' => 'Tonga',
                'TT' => 'Trinidad And Tobago',
                'TN' => 'Tunisia',
                'TR' => 'Turkey',
                'TM' => 'Turkmenistan',
                'TC' => 'Turks And Caicos Islands',
                'TV' => 'Tuvalu',
                'UG' => 'Uganda',
                'UA' => 'Ukraine',
                'AE' => 'United Arab Emirates',
                'GB' => 'United Kingdom',
                'US' => 'United States',
                'UM' => 'United States Outlying Islands',
                'UY' => 'Uruguay',
                'UZ' => 'Uzbekistan',
                'VU' => 'Vanuatu',
                'VE' => 'Venezuela',
                'VN' => 'Viet Nam',
                'VG' => 'Virgin Islands, British',
                'VI' => 'Virgin Islands, U.S.',
                'WF' => 'Wallis And Futuna',
                'EH' => 'Western Sahara',
                'YE' => 'Yemen',
                'ZM' => 'Zambia',
                'ZW' => 'Zimbabwe',
            );
        }

        public function calculateTotalPage($count, $limit) {
            return ($count - ($count % $limit)) / $limit + 1;
        }

        function calculatePagination(\MPW\MPW $gb, $totalRow) {
            $rowLimitDefault = Data::ROW_LIMIT_DEFAULT;
            $rowLimitMax = Data::ROW_LIMIT_MAX;

            $rowLimit = $gb->GL->GET_int('rowLimit');
            if ($rowLimit === false || $rowLimit <= 0) $rowLimit = $rowLimitDefault;
            if ($rowLimit > $rowLimitMax) $rowLimit = $rowLimitMax;

            $totalPage = $gb->GL->calculateTotalPage($totalRow, $rowLimit);

            $pageNum = $gb->GL->GET_int('pageNum');
            if ($pageNum === false || $pageNum <= 0) $pageNum = 1;

            if ($pageNum > $totalPage) $pageNum = $totalPage;

            $offset = ($pageNum - 1) * $rowLimit;

            return array(
                'rowLimit' => $rowLimit,
                'totalPage' => $totalPage,
                'pageNum' => $pageNum,
                'offset' => $offset
            );
        }

        public function createUrlFriendlyLink($data) {
            $data = strtolower($data);
            $data = str_replace(' ', '-', $data);
            return trim($data);
        }
    }

    class LG {
        //FIXME: değişkenler bu sınıf içinde olmaması lazım MPW genel bir sınıf ve tüm projelerde kullanılacak
        private $lng = ['en', 'tr'];

        public $accessories;
        public $language;

        public function getLng() {
            return isset($_SESSION['language']) ? $_SESSION['language'] : $this->lng[0];
        }

        /**
         * @param $l string 'en' or 'tr' etc
         */
        public function setLng($l) {
            $l = trim($l);
            $language = in_array($l, $this->lng) ? $l : $this->lng[0];
            $_SESSION['language'] = $language;
        }

        public function is($l) {
            return $this->getLng() == $l;
        }

        public function load() {
            $index = array_search($this->getLng(), $this->lng);
            if ($index === false) return;

            $this->accessories = ['Accessories', 'Aksesuarlar'][$index];
            $this->language = ['Language', 'Dil'][$index];
        }
    }

    class GA {
        //Google Analytics

        public $debug = false;

        private $tid;
        private $cid;
        private $transactionId;

        /**
         * GA constructor.
         * @param $tid
         * @param $cid
         * @param $transactionId
         */
        public function init($tid, $cid, $transactionId) {
            $this->tid = urlencode($tid);
            $this->cid = urlencode($cid);
            $this->transactionId = urlencode($transactionId);
        }

        public function sendTransaction() {
            return $this->send(
                'v=1' .
                '&t=transaction' .
                '&tid=' . $this->tid .
                '&cid=' . $this->cid .
                '&ti=' . $this->transactionId);
        }

        public function sendItem($in, $ip, $iq, $ic, $cu) {
            return $this->send(
                'v=1' .
                '&t=item' .
                '&tid=' . $this->tid .
                '&cid=' . $this->cid .
                '&ti=' . $this->transactionId .
                '&in=' . urlencode($in) .
                '&ip=' . urlencode($ip) .
                '&iq=' . urlencode($iq) .
                '&ic=' . urlencode($ic) .
                '&cu=' . urlencode($cu)
            );
        }

        private function send($data) {
            $url = $this->debug ? 'http://www.google-analytics.com/debug/collect?' : 'http://www.google-analytics.com/collect?';

            var_dump($url . $data);

            $ch = curl_init($url . $data);
            //curl_setopt($ch, CURLOPT_POST, 0);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            return curl_exec($ch);
        }
    }
}