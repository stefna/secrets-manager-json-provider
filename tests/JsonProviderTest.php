<?php declare(strict_types=1);

namespace Stefna\SecretsManager\Provider\JsonProvider\Tests;

use PHPUnit\Framework\TestCase;
use Stefna\SecretsManager\Exceptions\SecretNotFoundException;
use Stefna\SecretsManager\Provider\JsonProvider\JsonProvider;
use Stefna\SecretsManager\Values\Secret;

final class JsonProviderTest extends TestCase
{
	public function testParseFile(): void
	{
		$provider = new JsonProvider(__DIR__ . '/fixtures/secrets.json');

		$secret = $provider->getSecret('secret-key');
		$this->assertSame('secret value', $secret->getValue());

		$secretComplex = $provider->getSecret('secret-key-complex');
		$this->assertTrue(isset($secretComplex['key']));

		$this->assertNotEmpty($secretComplex->getMetadata());
	}

	public function testParseData(): void
	{
		$secretFile = (string)tempnam(sys_get_temp_dir(), 'json-provider-test-');
		unlink($secretFile);
		$provider = new JsonProvider($secretFile, [
			[
				'key' => 'keep-key',
				'value' => 'keep-value',
			],
		]);

		$secret = $provider->getSecret('keep-key');

		$this->assertSame('keep-value', $secret->getValue());
	}

	public function testPutSecretPersisting(): void
	{
		$secretFile = $this->getSecretsFile();
		$provider = new JsonProvider($secretFile);

		$testValue = 'value';
		$testKey = 'test-key';
		$provider->putSecret(new Secret($testKey, $testValue));

		$newProvider = new JsonProvider($secretFile);

		$secret = $newProvider->getSecret($testKey);
		$this->assertSame($testValue, $secret->getValue());
	}

	public function testDeleteSecretPersisting(): void
	{
		$secretFile = $this->getSecretsFile([
			[
				'key' => 'delete-key',
				'value' => 'delete-value',
			],
			[
				'key' => 'keep-key',
				'value' => 'keep-value',
			],
		]);
		$provider = new JsonProvider($secretFile);

		$deleteKey = 'delete-key';
		$secret = $provider->getSecret($deleteKey);
		$provider->deleteSecret($secret);

		try {
			$provider->getSecret($deleteKey);
			$this->fail('Key should have been deleted');
		}
		catch (SecretNotFoundException $e) {
		}

		try {
			$newProvider = new JsonProvider($secretFile);

			$keepSecret = $newProvider->getSecret('keep-key');
			$this->assertSame('keep-value', $keepSecret->getValue());

			$newProvider->getSecret($deleteKey);
			$this->fail('Key should have been deleted from file');
		}
		catch (SecretNotFoundException $e) {
			$this->assertTrue(true);
		}
	}

	/**
	 * @param mixed[] $data
	 */
	private function getSecretsFile(array $data = []): string
	{
		$secretFile = (string)tempnam(sys_get_temp_dir(), 'json-provider-test-');
		file_put_contents($secretFile, json_encode($data));
		return $secretFile;
	}
}
