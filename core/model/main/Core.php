<?php

	namespace model\main;

	use Exception;
	use Generator;
	use Gettext\Generator\ArrayGenerator;
	use Gettext\Generator\JsonGenerator;
	use Gettext\Generator\MoGenerator;
	use Gettext\GettextTranslator;
	use Gettext\Loader\PoLoader;
	use Gettext\Translator;
	use Gettext\TranslatorFunctions;
	use model\Events\Event;
	use model\helper\CsvTable;
	use model\page\TmpPage;
	use PDO;
	use PHPMailer\PHPMailer\PHPMailer;
	use SmartyBC;
	use tables\Users;
	use Traineratwot\Cache\Cache;
	use Traineratwot\config\Config;
	use Traineratwot\PDOExtended\Dsn;
	use Traineratwot\PDOExtended\exceptions\SqlBuildException;
	use Traineratwot\PDOExtended\PDOE;
	use traits\validators\ExceptionValidate;

	/**
	 * Основной класс
	 */
	final class Core implements ErrorPage
	{
		/**
		 * @var PDOE
		 */
		public PDOE   $db;
		public ?Users $user = NULL;
		/**
		 * @var SmartyBC|null
		 */
		public ?SmartyBC $smarty;
		public bool      $isAuthenticated = FALSE;
		/**
		 * @var Cache
		 */
		public Cache $cache;
		/**
		 * Переводы текущей локали
		 * @var mixed|null
		 */
		public        $translation;
		private array $_cache = [];

		public function __construct()
		{
			Event::emit('BeforeAppInit', $this);
			try {

				$dsn = new Dsn();
				$dsn->setDriver(Config::get('TYPE_DB'));
				$dsn->setDatabase(Config::get('DATABASE_DB'));
				if (Config::get('CHARSET_DB')) {
					$dsn->setCharset(Config::get('CHARSET_DB'));
				}
				if (Config::get('HOST_DB')) {
					$dsn->setHost(Config::get('HOST_DB'));
				} else {
					$dsn->setSocket(Config::get('SOCKET_DB'));
				}
				$dsn->setPort((int)Config::get('PORT_DB'));
				$dsn->setPassword(Config::get('PASS_DB'));
				$dsn->setUsername(Config::get('USER_DB'));
				$this->db = PDOE::init($dsn);
				if (Config::get('SQL_LOG')) {
					$this->db->logOn();
				}
				$this->auth();
			} catch (Exception $e) {
				if (!Event::emit('onDataBaseError', ['core' => $this, 'error' => $e])) {
					Err::fatal($e->getMessage(), 0, 0, $e);
				}
			}
			$this->cache = new Cache();
			Event::emit('AfterAppInit', $this);
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
		 * Check authorization
		 * @return void
		 */
		public function auth(User &$user = NULL)
		{
			if ($user) {
				$user->login();
				$this->user = &$user;
				if ($this->user === NULL) {
					$this->isAuthenticated = FALSE;
				} else {
					$this->isAuthenticated = TRUE;
				}
				return;
			}
			if (isset($_SESSION['authKey']) && $_SESSION['authKey'] && $_SESSION['ip'] === Utilities::getIp()) {
				$u = $this->getUser(['authKey' => $_SESSION['authKey']]);
				if (!$u->isNew) {
					$this->user = &$u;
				} else {
					$u->logout();
				}
			} else {
				$authKey = strip_tags($_COOKIE['authKey']);
				$id      = (int)$_COOKIE['userId'];
				$u       = $this->getUser($id);
				if (!$u->isNew && $authKey === Utilities::hash($u->get('authKey') . Utilities::getIp())) {
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
		 * @param mixed $where
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
		 * @param array                            $options cc, bcc , from
		 * @return bool|string
		 */
		public function mail($to, string $subject, string $body, array $file = [], array $options = [])
		{
			$mail = NULL;
			try {
				$mail = new PHPMailer(TRUE);
				$mail->isHTML(TRUE);
				$mail->setLanguage('ru');
				$mail->CharSet = PHPMailer::CHARSET_UTF8;
				if (!empty($options['from'])) {
					if ($options['from'] instanceof User) {
						$email = $options['from']->getEmail();
						$name  = $options['from']->getName();
					} else {
						$a     = explode('::', $options['from']);
						$email = $a[0];
						$name  = $a[1];
					}
					$mail->setFrom($email, $name);
				} else {
					$mail->setFrom(Config::get('FROM_EMAIL_MAIL'), Config::get('FROM_NAME_MAIL'));
				}
				if (Config::get('SMTP_MAIL')) {
					$mail->isSMTP();                                                        //Send using SMTP
					$mail->Host = Config::get('HOST_MAIL');                                 //Set the SMTP server to send through
					if (Config::get('AUTH_MAIL')) {
						$mail->SMTPAuth = TRUE;                                                       //Enable SMTP authentication
						$mail->Username = Config::get('USERNAME_MAIL');                               //SMTP username
						$mail->Password = Config::get('PASSWORD_MAIL');                               //SMTP password
					}
					$mail->SMTPSecure = Config::get('SECURE_MAIL');                                  //Enable implicit TLS encryption
					$mail->Port       = Config::get('PORT_MAIL');                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
				}
				if (!is_array($to)) {
					$to = [$to];
				}
				foreach ($to as $too) {
					if ($too instanceof User) {
						$email = $too->getEmail();
						$name  = $too->getName();
					} else {
						$a     = explode('::', $too);
						$email = $a[0];
						$name  = $a[1];
					}
					$mail->addAddress($email, $name);
				}
				if (!empty($options['cc'])) {
					$copy = $options['cc'];
					if (!is_array($copy)) {
						$copy = [$copy];
					}
					foreach ($copy as $too) {
						if ($too instanceof User) {
							$email = $too->getEmail();
							$name  = $too->getName();
						} else {
							$a     = explode('::', $too);
							$email = $a[0];
							$name  = $a[1];
						}
						$mail->addCC($email, $name);
					}
				}
				if (!empty($options['bcc'])) {
					$copy = $options['bcc'];
					if (!is_array($copy)) {
						$copy = [$copy];
					}
					foreach ($copy as $too) {
						if ($too instanceof User) {
							$email = $too->getEmail();
							$name  = $too->getName();
						} else {
							$a     = explode('::', $too);
							$email = $a[0];
							$name  = $a[1];
						}
						$mail->addBCC($email, $name);
					}
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
				Event::emit('BeforeMailSend', $mail);
				$mail->send();
				Event::emit('AfterMailSend', $mail);
				return TRUE;
			} catch (\PHPMailer\PHPMailer\Exception $e) {
				Event::emit('onEmailSendError', ['mail' => $mail, 'error' => $e]);
				Err::error($e->getMessage());
				return $mail->ErrorInfo;
			}
		}

		/**
		 * @template T of \BdObject
		 * @param class-string<T>  $class
		 * @param array|string|int $where
		 * @param boolean          $cache
		 * @return BdObject|T
		 * @throws Exception
		 */
		public function getObject(string $class, $where = [], bool $cache = TRUE)
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
		public function getCollection(string $class, array $where = [], $order_by = NULL, string $order_dir = 'ASC')
		{
			$data     = [];
			$class    = self::getClass($class);
			$cls      = new $class($this);
			$order_by = $order_by ?: $cls->primaryKey;
			if (empty($where)) {
				$sql = "SELECT * FROM `$cls->table` ORDER BY `$order_by` $order_dir";
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
					$c->fromArray($row, FALSE);
					$data[] = $c;
				}
			}
			return $data;
		}
		/**
		 * @template T of \BdObject
		 * @param class-string<T> $class extends BdObject
		 * @param array           $where
		 * @param null            $order_by
		 * @param string          $order_dir
		 * @return Generator<T>
		 * @throws SqlBuildException
		 */
		public function getIterator(string $class, array $where = [], $order_by = NULL, string $order_dir = 'ASC')
		{
			$data     = [];
			$class    = self::getClass($class);
			$cls      = new $class($this);
			$order_by = $order_by ?: $cls->primaryKey;
			if (empty($where)) {
				$sql = "SELECT * FROM `$cls->table` ORDER BY `$order_by` $order_dir";
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
					$c->fromArray($row, FALSE);
					yield $c;
				}
			}
		}

		/**
		 * @param      $_lang
		 * @param bool $_gt //use gettext
		 * @return string
		 * @throws ExceptionValidate
		 */
		public function setLocale($_lang, bool $_gt = TRUE)
		{
			putenv("LANG_FOLDER=$_lang");
			$lang = setlocale(LC_ALL, $_lang);
			if ($lang === FALSE) {
				Err::error("Can't set locale to '$_lang'");
				$lang = setlocale(LC_ALL, $_lang, substr($_lang, 0, 2) . '.utf8', substr($_lang, 0, 5) . '.utf8');
			}
			$php = '';
			if ($_gt && $lang) {
				$domain = Config::get('LOCALE_DOMAIN');
				$po     = Config::get('LOCALE_PATH') . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".po";
				if (file_exists($po)) {
					$json = Config::get('LOCALE_PATH') . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".json";
					if (!file_exists($json)) {
						$translations = (new PoLoader())->loadFile($po);
						(new JsonGenerator())->generateFile($translations, $json);
					}
					if (Config::get('USE_GETTEXT')) {
						$mo = Config::get('LOCALE_PATH') . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".mo";
						if (!file_exists($mo)) {
							$translations = (new PoLoader())->loadFile($po);
							(new MoGenerator())->generateFile($translations, $mo);
						}
						$this->translation = Utilities::jsonValidate(file_get_contents($json));
					} else {
						$php = Config::get('LOCALE_PATH') . $_lang . DIRECTORY_SEPARATOR . "LC_MESSAGES" . DIRECTORY_SEPARATOR . $domain . ".php";
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
			if (Config::get('USE_GETTEXT')) {
				$t = new GettextTranslator();
				if ($lang) {
					$t->setLanguage($lang);
				}
				$t->loadDomain(Config::get('LOCALE_DOMAIN'), Config::get('LOCALE_PATH'));
				bindtextdomain(Config::get('LOCALE_DOMAIN'), Config::get('LOCALE_PATH'));
				textdomain(Config::get('LOCALE_DOMAIN'));
			} else {
				$t = new Translator();
				$t->defaultDomain(Config::get('LOCALE_DOMAIN'));
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
			ob_end_clean();
			if ($page = $errPage->render(TRUE)) {
				exit($page);
			}
			if (file_exists(Config::get('PAGES_PATH') . 'errors/' . $code . '.html')) {
				readfile(Config::get('PAGES_PATH') . 'errors/' . $code . '.html');
			} else {
				readfile(Config::get('PAGES_PATH') . 'errors/' . '404.html');
			}
			exit;
		}
	}