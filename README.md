# Stefna Secrets Manager - JSON File Adapter

JSON File Adapter for [Stefna Secrets Manager](https://bitbucket.org/stefnadev/secrets-manager)

## Table of Contents

1. [Installation](#installation)
2. [Secret Structure](#secrets-file-structure)

### Installation

```bash
$ composer require stefna/secrets-manager-core stefna/secrets-manager-json-provider
```

### Secrets File Structure

```json
[
	{
		"key": "my-secret-key",
		"value": "some secret"
	},
	{
		"key": "some-other-secret",
		"value": {
			"a": "b"
		},
		"metadata": {
			"foo": "bar"
		}
	}
]
```

or

```json
{
	"my-secret-key": {
		"key": "my-secret-key",
		"value": "some secret"
	},
	"some-other-secret": {
		"key": "some-other-secret",
		"value": {
			"a": "b"
		},
		"metadata": {
			"foo": "bar"
		}
	}
}
```
