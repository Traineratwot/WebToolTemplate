<?php

	namespace model\main;

	use Exception;
	use Gettext\Generator\ArrayGenerator;
	use Gettext\Generator\JsonGenerator;
	use Gettext\Generator\MoGenerator;
	use Gettext\GettextTranslator;
	use Gettext\Loader\PoLoader;
	use Gettext\Translator;
	use Gettext\TranslatorFunctions;
	use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
	use PDO;
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use SmartyBC;
	use tables\Users;
	use traits\Utilities;

	/**
	 * Основной класс
	 */
	final class Core implements ErrorPage
	{
		use Utilities;

		public    $db     = NULL;
		public    $user   = NULL;
		protected $_cache = [];
		/**
		 * @var SmartyBC
		 */
		public $smarty;
		public $isAuthenticated = FALSE;
		/**
		 * @var Cache
		 */
		public $cache;
		/**
		 * Переводы текущей локали
		 * @var mixed
		 */
		public $translation;

		public function __construct()
		{
			try {
				$this->db = new PDOExtended(WT_DSN_DB, WT_USER_DB, WT_PASS_DB);
			} catch (PDOException $e) {
				Err::fatal($e->getMessage(), __LINE__, __FILE__);
			}
			$this->auth();
			$this->cache = new Cache();
		}

		/**
		 * Check autorization
		 * @return void
		 */
		public function auth()
		{
			if (isset($_SESSION['authKey']) and $_SESSION['authKey'] and $_SESSION['ip'] == self::getIp()) {
				$u = $this->getUser(['authKey' => $_SESSION['authKey']]);
				if (!$u->isNew) {
					$this->user = &$u;
				} else {
					session_unset();
				}
			} else {
				$authKey = strip_tags($_COOKIE['authKey']);
				$id      = (int)$_COOKIE['userId'];
				$u       = $this->getUser($id);
				if (!$u->isNew) {
					if ($authKey == hash('sha256', $u->get('authKey') . self::getIp())) {
						$this->user = &$u;
						$this->user->login();
					}
				}
			}
			if ($this->user == NULL) {
				$this->isAuthenticated = FALSE;
			} else {
				$this->isAuthenticated = TRUE;
			}
		}

		/**
		 * Simple work with csv table
		 * @return table
		 */
		public function newTable()
		{
			return new table();
		}

		/**
		 * @param $where
		 * @return Users
		 */
		public function getUser($where = [])
		{
			return new Users($this, $where);
		}

		/**
		 * @param array<string|users>|string|users $to
		 * @param string                           $subject
		 * @param string                           $body
		 * @param array                            $file
		 * @param array                            $options
		 * @return bool|string
		 */
		public function mail($to, $subject, $body, $file = [], $options = [])
		{
			try {
				$mail = new PHPMailer(TRUE);
				$mail->isHTML(TRUE);
				$mail->setLanguage('ru');
				$mail->CharSet = PHPMailer::CHARSET_UTF8;
				if (!empty($options['from'])) {
					if ($options['from'] instanceof Users) {
						$email = $options['from']->get('email');
						$name  = $options['from']->get('full_name');
					} else {
						$a     = explode('::', $options['from']);
						$email = $a[0];
						$name  = $a[1];
					}
					$mail->setFrom($email, $name);
				} else {
					$mail->setFrom(WT_FROM_EMAIL_MAIL, WT_FROM_NAME_MAIL);
				}
				if (WT_SMTP_MAIL) {
					$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
					$mail->isSMTP();                                            //Send using SMTP
					$mail->Host = WT_HOST_MAIL;                                 //Set the SMTP server to send through
					if (WT_AUTH_MAIL) {
						$mail->SMTPAuth = TRUE;                                           //Enable SMTP authentication
						$mail->Username = WT_USERNAME_MAIL;                               //SMTP username
						$mail->Password = WT_PASSWORD_MAIL;                               //SMTP password
					}
					$mail->SMTPSecure = WT_SECURE_MAIL;                                  //Enable implicit TLS encryption
					$mail->Port       = WT_PORT_MAIL;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
				}

				if (!is_array($to)) {
					$to = [$to];
				}
				foreach ($to as $too) {
					if ($too instanceof Users) {
						$email = $too->get('email');
						$name  = $too->get('full_name');
					} else {
						$a     = explode('::', $too);
						$email = $a[0];
						$name  = $a[1];
					}
					$mail->addAddress($email, $name);
				}
				if (!is_array($file)) {
					$file = [$file];
				}
				foreach ($file as $f) {
					$mail->addAttachment($f);
				}
				$mail->Subject = $subject;
				$mail->Body    = $body;
				$mail->AltBody = strip_tags($body);
				$mail->send();
				return TRUE;
			} catch (\PHPMailer\PHPMailer\Exception $e) {
				return $mail->ErrorInfo;
			}
		}

		public static function getClass($class)
		{
			if (class_exists($class)) {
				return $class;
			} else {
				$class = "core\classes\\tables\\$class";
				if (class_exists($class)) {
					return $class;
				} else {
					Err::fatal($class . " not exists", __LINE__, __FILE__);
				}
			}
		}

		/**
		 * @template T of \BdObject
		 * @param class-string<T> $class
		 * @param array           $where
		 * @param booean          $cache
		 * @return T
		 * @throws Exception
		 */
		public function getObject($class, $where = [], $cache = TRUE)
		{
			$class = Core::getClass($class);
			if (!$cache or empty($where)) {
				return new $class($this, $where);
			}
			$key = md5(serialize($where));
			if (!isset($this->_cache[$class]) or !isset($this->_cache[$class][$key])) {
				$this->_cache[$class][$key] = new $class($this, $where);
			}
			return $this->_cache[$class][$key];
		}

		/**
		 * @template T of \BdObject
		 * @param class-string<T> $class extends BdObject
		 * @param array           $where
		 * @return   [T]
		 * @throws Exception
		 */
		public function getCollection($class, $where = [], $order_by = NULL, $order_dir = 'ASC')
		{
			$data     = [];
			$class    = Core::getClass($class);
			$cls      = new $class($this);
			$order_by = $order_by ?: $cls->primaryKey;
			if (empty($where)) {
				$sql = "SELECT * FROM `{$cls->table}` ORDER BY `{$order_by}` $order_dir";
			} else {
				$builder = new GenericBuilder();
				$query   = $builder->select()
								   ->setTable($cls->table)
								   ->orderBy($order_by, $order_dir)
								   ->where();
				foreach ($where as $key => $value) {
					$query->equals($key, $value);
				}
				$query  = $query->end();
				$sql    = $builder->write($query);
				$values = $builder->getValues();
				$values = BdObject::prepareBinds($values);
				$sql    = BdObject::prepareSql($sql, $cls->table);
				$sql    = strtr($sql, $values);
			}
			$q = $this->db->query($sql);
			if ($q) {
				while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
					$c = new $class($this);
					/** @var BdObject $c */
					$c->fromArray($row, FALSE);
					$data[] = $c;
				}
			}
			return $data;

		}

		/**
		 * @param $lang //locale code
		 * @param $_gt  //use gettext
		 * @return string|false
		 */
		public function setLocale($_lang, $_gt = TRUE)
		{
			putenv("LANG_FOLDER=$_lang");
			$lang = setlocale(LC_ALL, $_lang);
			if ($lang === FALSE) {
				Err::error("Can't set locale to '{$_lang}'", __LINE__, __FILE__);
				$lang = setlocale(LC_ALL, $_lang, substr($_lang, 0, 2) . '.utf8', substr($_lang, 0, 5) . '.utf8');
			}
			if ($_gt and $lang) {
				$domain = WT_LOCALE_DOMAIN;
				$po     = WT_LOCALE_PATH . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".po";
				if (file_exists($po)) {
					$json = WT_LOCALE_PATH . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".json";
					if (!file_exists($json)) {
						$translations = (new PoLoader())->loadFile($po);
						(new JsonGenerator())->generateFile($translations, $json);
					}
					if (WT_USE_GETTEXT) {
						$mo = WT_LOCALE_PATH . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".mo";
						if (!file_exists($mo)) {
							$translations = (new PoLoader())->loadFile($po);
							(new MoGenerator())->generateFile($translations, $mo);
						}
						$this->translation = json_decode(file_get_contents($json), TRUE);
					} else {
						$php = WT_LOCALE_PATH . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".php";
						if (!file_exists($php)) {
							$translations = (new PoLoader())->loadFile($po);
							(new ArrayGenerator())->generateFile($translations, $php);
						}
						$this->translation = include $php;
					}
				}
				// Задаем текущий язык проекта
				putenv("LANGUAGE=$lang");
				putenv("LANG=$lang");
				putenv("LC_ALL=$lang");
				header('X-locale: ' . $lang);
			} else {
				header('X-locale: ' . $_lang);
			}
			if (WT_USE_GETTEXT) {
				$t = new GettextTranslator();
				if ($lang) {
					$t->setLanguage($lang);
				}
				$t->loadDomain(WT_LOCALE_DOMAIN, WT_LOCALE_PATH);
				bindtextdomain(WT_LOCALE_DOMAIN, WT_LOCALE_PATH);
				textdomain(WT_LOCALE_DOMAIN);
			} else {
				$t = new Translator();
				$t->defaultDomain(WT_LOCALE_DOMAIN);
				if (file_exists($php)) {
					$t->loadTranslations($php);
				}
				TranslatorFunctions::register($t);
			}
			return $lang;
		}

		public static function __set_state($arr)
		{
			global $core;
			return $core;
		}

		public function errorPage($code = 404, $msg = 'Not Found')
		{
			header("HTTP/1.1 $code $msg");
			$errPage = new TmpPage($this, 'errors/' . $code);
			$errPage->setVar('code', $code);
			$errPage->setVar('msg', $msg);
			if ($page = $errPage->render(TRUE)) {
				ob_end_clean();
				exit($page);
			} else {
				ob_end_clean();
				if (file_exists(WT_PAGES_PATH . 'errors/' . $code . '.html')) {
					readfile(WT_PAGES_PATH . 'errors/' . $code . '.html');
				} else {
					readfile(WT_PAGES_PATH . 'errors/' . '404.html');
				}
				exit;
			}
		}
	}