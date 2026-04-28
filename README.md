
---

# FioClient PHP SDK

A PHP client for accessing the [Fio Bank API](https://www.fio.cz/), supporting retrieval of account statements and transactions in JSON, XML, or CSV format. The client provides raw responses as well as typed DTOs for easy consumption.

## Installation

Install via Composer:

```bash
composer require your-vendor/fio-client
```

> Requires PHP 7.4+.

## Usage

### Basic Setup

```php
use Firewalker\FioClient\Client\FioClient;
use GuzzleHttp\Client as HttpClient;
use Http\Discovery\Psr17FactoryDiscovery;

$httpClient = new HttpClient(); // any PSR-18 client
$requestFactory = Psr17FactoryDiscovery::findRequestFactory();

$token = 'YOUR_FIO_API_TOKEN';
$fio = new FioClient($token, $httpClient, $requestFactory);
```

### Fetch Transactions (Raw JSON)

```php
$from = new \DateTimeImmutable('2024-01-01');
$to = new \DateTimeImmutable('2024-01-31');

$raw = $fio->getTransactionsRaw($from, $to); // string
$data = json_decode($raw, true);             // array
```

### Fetch Transactions as DTO

```php
use Firewalker\FioClient\Dto\AccountStatementDto;

$dto = $fio->getTransactionsDTO($from, $to); // AccountStatementDto

echo $dto->accountNumber;
echo count($dto->transactions);
```

### Fetch Last Transactions

```php
$rawLast = $fio->getLastTransactionsRaw();
$dtoLast = $fio->getLastTransactionsDTO();
```

### Fetch Transactions Since a Specific ID

```php
$rawSince = $fio->getTransactionsSinceIdRaw(12345);
$dtoSince = $fio->getTransactionsSinceIdDTO(12345);
```

### Set Last Transaction ID

```php
$response = $fio->setLastId(12345); // raw API response
```

### Supported Formats

You can get responses in:

* JSON (default)
* XML
* CSV

```php
$xml = $fio->getTransactionsRaw($from, $to, FioClient::FORMAT_XML);
$csv = $fio->getTransactionsRaw($from, $to, FioClient::FORMAT_CSV);
```

## Exceptions

* **`App\Exception\HttpException`** — HTTP response code >= 300.
* **`App\Exception\InvalidFormatException`** — invalid format passed to request.
* **`App\Exception\JsonException`** — invalid API response structure.
* **`\JsonException`** — native PHP JSON decoding errors.
* **`Psr\Http\Client\ClientExceptionInterface`** — HTTP client errors.

## DTOs

The main DTO is `AccountStatementDto`, which contains:

* `accountId`
* `accountNumber`
* `currency`
* `transactions` — array of `TransactionDto` objects

Each `TransactionDto` contains details such as:

* `transactionId`
* `date`
* `amount`
* `counterpartyAccount`
* `counterpartyName`
* `variableSymbol`
* `message`
* `type`
* `performedBy`

Use DTOs when you want typed access to transaction data. Otherwise, raw JSON gives you the full API response as an array.

## Example: Iterate Transactions

```php
$statement = $fio->getTransactionsDTO($from, $to);

foreach ($statement->transactions as $tx) {
    echo sprintf(
        "%s: %s %.2f (%s)\n",
        $tx->date,
        $tx->counterpartyName,
        $tx->amount,
        $tx->message ?? ''
    );
}
```

## Notes

* All methods are **PSR-18 / PSR-17 compatible**, so any HTTP client implementing these standards works.
* DTO methods always return **JSON-decoded data**, even if the raw request was in XML or CSV, so JSON is recommended for DTOs.
* The client throws **explicit exceptions** for HTTP, invalid formats, and JSON errors.

## License

MIT

---
