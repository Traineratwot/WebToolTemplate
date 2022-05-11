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
	use model\helper\CsvTable;
	use model\page\TmpPage;
	use PDO;
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use SmartyBC;
	use tables\Users;
	use Traineratwot\Cache\Cache;
	use Traineratwot\PDOExtended\Dsn;
	use Traineratwot\PDOExtended\exceptions\SqlBuildException;
	use Traineratwot\PDOExtended\PDOE;

	/**
	 * Основной класс
	 */
//	define('WT_DSN_DB', WT_TYPE_DB . ":host=" . WT_HOST_DB . ";port=" . WT_PORT_DB . ";dbname=" . WT_DATABASE_DB.";charset=". WT_CHARSET_DB);

	final class Core implements ErrorPage
	{
		/**
		 * @var PDOE
		 */
		public $db;
		public $user;
		/**
		 * @var SmartyBC|null
		 */
		public $smarty;
		public $isAuthenticated = FALSE;
		/**
		 * @var Cache
		 */
		public $cache;
		/**
		 * Переводы текущей локали
		 * @var mixed|null
		 */
		public  $translation;
		private $_cache = [];

		public function __construct()
		{
			try {
				$dsn = new Dsn();
				$dsn->setDriver(WT_TYPE_DB);
				$dsn->setDatabase(WT_DATABASE_DB);
				if (WT_CHARSET_DB) {
					$dsn->setCharset(WT_CHARSET_DB);
				}
				if (WT_HOST_DB) {
					$dsn->setHost(WT_HOST_DB);
				} else {
					$dsn->setSocket(WT_SOCKET_DB);
				}
				$dsn->setPort((int)WT_PORT_DB);
				$dsn->setPassword(WT_PASS_DB);
				$dsn->setUsername(WT_USER_DB);
				$this->db = new PDOE($dsn);
				if (WT_SQL_LOG) {
					$this->db->logOn();
				}
				$this->auth();
			} catch (Exception $e) {
				Err::error($e->getMessage(), 0, 0);
			}
			$this->cache = new Cache();
		}

		/**
		 * Check authorization
		 * @return void
		 */
		public function auth()
		{
			if (isset($_SESSION['authKey']) && $_SESSION['authKey'] && $_SESSION['ip'] === Utilities::getIp()) {
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
				if (!$u->isNew && $authKey === hash('sha256', $u->get('authKey') . Utilities::getIp())) {
					$this->user = &$u;
					$this->user->login();
				}
			}
			if ($this->user === NULL) {
				$this->isAuthenticated = FALSE;
			} else {
				$this->isAuthenticated = TRUE;
			}
		}

		/**
		 * @param $where
		 * @return Users
		 */
		public function getUser($where = [])
		{
			return new Users($this, $where);
		}

		public static function __set_state($arr)
		{
			global $core;
			return $core;
		}

		/**
		 * @return self
		 */
		public static function init()
		{
			if (array_key_exists('core', $GLOBALS)) {
				return $GLOBALS['core'];
			}

			global $core;
			$core = new self();
			return $core;
		}

		/**
		 * Simple work with csv table
		 * @return CsvTable
		 */
		public function newTable()
		{
			return new CsvTable();
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
				Err::error($e->getMessage());
				return $mail->ErrorInfo;
			}
		}

		/**
		 * @template T of \BdObject
		 * @param class-string<T> $class
		 * @param array           $where
		 * @param boolean         $cache
		 * @return BdObject|T
		 * @throws Exception
		 */
		public function getObject($class, $where = [], $cache = TRUE)
		{
			$class = self::getClass($class);
			if (!$cache || empty($where)) {
				return new $class($this, $where);
			}
			$key = Utilities::hash($where);
			if (!isset($this->_cache[$class][$key])) {
				$this->_cache[$class][$key] = new $class($this, $where);
			}
			return $this->_cache[$class][$key];
		}

		public static function getClass($class)
		{
			if (class_exists($class)) {
				return $class;
			}
			$class = "tables\\$class";
			if (class_exists($class)) {
				return $class;
			}
			Err::fatal($class . " not exists");
			return $class;
		}

		/**
		 * @template T of \BdObject
		 * @param class-string<T> $class extends BdObject
		 * @param array           $where
		 * @param null            $order_by
		 * @param string          $order_dir
		 * @return array [T]
		 * @throws SqlBuildException
		 */
		public function getCollection($class, $where = [], $order_by = NULL, $order_dir = 'ASC')
		{
			$data     = [];
			$class    = self::getClass($class);
			$cls      = new $class($this);
			$order_by = $order_by ?: $cls->primaryKey;
			if (empty($where)) {
				$sql = "SELECT * FROM `{$cls->table}` ORDER BY `{$order_by}` $order_dir";
			} else {
				$builder = $this->db->table($cls->table);
				$query   = $builder->select()
								   ->orderBy([$order_by => $order_dir])
								   ->where()
				;
				foreach ($where as $key => $value) {
					$query->eq($key, $value);
				}
				$sql = $query->end()->toSql();
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
		 * @param      $_lang
		 * @param bool $_gt //use gettext
		 * @return string
		 */
		public function setLocale($_lang, $_gt = TRUE)
		{
			putenv("LANG_FOLDER=$_lang");
			$lang = setlocale(LC_ALL, $_lang);
			if ($lang === FALSE) {
				Err::error("Can't set locale to '{$_lang}'");
				$lang = setlocale(LC_ALL, $_lang, substr($_lang, 0, 2) . '.utf8', substr($_lang, 0, 5) . '.utf8');
			}
			if ($_gt && $lang) {
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

		/**
		 * @throws Exception
		 */
		public function errorPage($code = 404, $msg = 'Not Found')
		{
			header("HTTP/1.1 $code $msg");
			$errPage = new TmpPage($this, 'errors/' . $code);
			$errPage->setVar('code', $code);
			$errPage->setVar('msg', $msg);
			if ($page = $errPage->render(TRUE)) {
				ob_end_clean();
				exit($page);
			}

			ob_end_clean();
			if (file_exists(WT_PAGES_PATH . 'errors/' . $code . '.html')) {
				readfile(WT_PAGES_PATH . 'errors/' . $code . '.html');
			} else {
				readfile(WT_PAGES_PATH . 'errors/' . '404.html');
			}
			exit;
		}
	}