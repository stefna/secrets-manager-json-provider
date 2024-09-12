<?php declare(strict_types=1);

namespace Stefna\SecretsManager\Provider\JsonProvider;

use Stefna\SecretsManager\Provider\ArrayProvider;
use Stefna\SecretsManager\Values\Secret;

final class JsonProvider extends ArrayProvider
{
	private const FIELD_KEY = 'key';
	private const FIELD_VALUE = 'value';
	private const FIELD_METADATA = 'metadata';

	private string $file;

	/**
	 * If $data is null will read file and parse content
	 *
	 * @param null|array<array-key, array{key: string, value: mixed, metadata?: mixed}> $data
	 */
	public function __construct(string $file, ?array $data = null)
	{
		if (!is_writable($file) && !is_writable(dirname($file))) {
			throw new \BadMethodCallException('File is not writeable');
		}
		$this->file = $file;
		if (is_array($data)) {
			$this->parseData($data);
		}
		else {
			$this->loadFile();
		}
	}

	public function putSecret(Secret $secret, ?array $options = []): Secret
	{
		parent::putSecret($secret, $options);
		$this->saveSecrets();
		return $secret;
	}

	public function deleteSecret(Secret $secret, ?array $options = []): void
	{
		parent::deleteSecret($secret, $options);
		$this->saveSecrets();
	}

	private function loadFile(): void
	{
		if (!file_exists($this->file)) {
			return;
		}
		$contents = file_get_contents($this->file);
		$json = json_decode((string)$contents, true);
		if (json_last_error()) {
			throw new \RuntimeException(json_last_error_msg());
		}
		$this->parseData($json);
	}

	/**
	 * @param array<int, array{key: string, value: mixed, metadata?: mixed}> $data
	 */
	private function parseData(array $data): void
	{
		foreach ($data as $info) {
			$secret = new Secret(
				$info[self::FIELD_KEY],
				$info[self::FIELD_VALUE],
				$info[self::FIELD_METADATA] ?? null
			);
			$this->data[$secret->getKey()] = $secret;
		}
	}

	private function saveSecrets(): void
	{
		$data = [];
		foreach ($this->data as $key => $secret) {
			$data[$key] = [
				self::FIELD_KEY => $secret->getKey(),
				self::FIELD_VALUE => $secret->getValue(),
				self::FIELD_METADATA => $secret->getMetadata(),
			];
		}
		file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	}
}
