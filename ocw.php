<?php
final class OCW
{
  protected $url;

  public function __construct($registry) {
    $this->url = $registry->get('url');
	}

	public function buildURL($route, $args = '', $connection = 'SSL')
	{
		if ($this->getVersion() < 1.5) {
			$url = ($connection == 'NONSSL') ? HTTP_SERVER : HTTPS_SERVER;
			$url .= 'index.php?route=' . $route;
			if ($args) {
				$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
			}
			return $url;
		} else {
			return $this->url->link($route, $args, $connection);
		}
	}

	public function getVersion()
	{
		if (!defined('VERSION') || VERSION < 1.5) {
			$version = 1.4;
		} elseif (defined('VERSION') && strpos(VERSION, '1.5') !== false) {
			$version = 1.5;
		}
		return $version;
	}
}
?>