# Commission Calculator

This project calculates commissions from a list of transactions. Transactions are provided in a text file with each transaction in JSON format. The code determines the commission based on the BIN number of the transaction to identify if the card is from an EU country, then applies the appropriate commission rate

## Requirements

* PHP 8.3 or higher
* Composer for dependency management

## Installation

1. Clone the repository:

    ```bash
    git clone git@github.com:soulshockers/commission-calculator.git
    cd commission-calculator
    ```
2. Install dependencies using Composer:

    ```bash
    composer install
    ```
3. Set up environment variables:

    Copy the .env.example file to .env and fill in the required credentials:

    ```bash
    cp .env.example .env
    ```

    Open the .env file and provide the necessary values:

    ```dotenv
    EU_COUNTRY_CODES=AT,BE,BG,CY,CZ,DE,DK,EE,ES,FI,FR,GR,HR,HU,IE,IT,LT,LU,LV,MT,NL,PO,PT,RO,SE,SI,SK
    EXCHANGE_RATES_URL=http://api.exchangeratesapi.io/v1/latest
    EXCHANGE_RATES_API_KEY=
    BIN_LOOKUP_URL=https://lookup.binlist.net
    ```
    
    Ensure you have valid API key in EXCHANGE_RATES_API_KEY.

## Running the Code

```bash
php bin/console tests/__data/input.txt
```

Replace tests/__data/input.txt with the path to your input file.

### Example Input File

An example input.txt file might look like this:

```text
{"bin":"45717360","amount":"100.00","currency":"EUR"}
{"bin":"516793","amount":"50.00","currency":"USD"}
{"bin":"45417360","amount":"10000.00","currency":"JPY"}
{"bin":"41417360","amount":"130.00","currency":"USD"}
{"bin":"4745030","amount":"2000.00","currency":"GBP"}
```

### Example Output
The output will be commissions calculated for each transaction. For example:
```bash
1
0.46
1.2
2.4
23.75
```


## Testing
Unit tests are provided to ensure the correctness of the functionality. To run the tests, use:

```bash
./vendor/bin/phpunit
```
### Test Coverage

Tests cover:

* Successful processing of transactions
* Handling of invalid JSON input
* Validation exceptions
* Commission calculations with different currencies and rates

## Code Structure

* src/Command/CalculateCommissionCommand.php: Symfony Console command that processes the input file and calculates commissions.
* src/Service/FileProcessor.php: Handles the logic for reading the file, deserializing JSON, and processing each transaction.
* src/Service/CommissionCalculator/CommissionCalculatorService.php: Calculates the commission based on BIN and currency.
* src/Service/CommissionCalculator/CommissionCalculationStrategy/: Contains strategies for commission calculation.
* tests/: Contains unit tests for the application.

## Notes

* Extensibility: The code is designed to be easily extendable. You can switch the currency rates provider or BIN lookup provider without modifying existing functionality.
* Error Handling: Errors are handled gracefully, with meaningful messages for issues such as invalid JSON or failed external service requests.
* Precision: Commission values are rounded to the nearest cent.

## License

This project is licensed under the MIT License.

## Time spend

Around 5 hours