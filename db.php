<?php
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$env = $_ENV["mode"];

define('DB_HOST', $_ENV[$env == 'production' ? "DB_HOST_PROD" : "DB_HOST"]);
define('DB_NAME', $_ENV[$env == 'production' ? "DB_NAME_PROD" : "DB_NAME"]);
define('DB_USER', $_ENV[$env == 'production' ? "DB_USER_PROD" : "DB_USER"]);
define('DB_PASS', $_ENV[$env == 'production' ? "DB_PASS_PROD" : "DB_PASS"]);
define('DB_CHAR', $_ENV[$env == 'production' ? "DB_CHAR_PROD" : "DB_CHAR"]);

class DB
{
	protected static $instance = null;

	protected function __construct()
	{
	}
	protected function __clone()
	{
	}

	public static function instance()
	{
		if (self::$instance === null) {
			$opt  = array(
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => FALSE,
			);
			$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
			self::$instance = new PDO($dsn, DB_USER, DB_PASS, $opt);
		}
		return self::$instance;
	}

	public static function __callStatic($method, $args)
	{
		return call_user_func_array(array(self::instance(), $method), $args);
	}

	public static function run($sql, $args = [])
	{
		if (!$args) {
			return self::instance()->query($sql);
		}
		$stmt = self::instance()->prepare($sql);
		$stmt->execute($args);
		return $stmt;
	}
}
