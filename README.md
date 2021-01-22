# Downloading hude amounts of data from DB via PHP

This set of classes will help you to implement iterative creation of files with huge amount of data from database.

# First steps

First of all create class wich will fetch data, process it and contain some utility methods:

```php
class HttpJob implements HttpJobInterface
{

    /**
     * Amount of data to be processed per one iteration
     *
     * @var integer
     */
    protected $limit = 1;

    /**
     * Processing data subset. In our case we store data in the CSV file
     * And if this file is not yet created, then create one
     */
    protected function process(array $data): void
    {
        if (! isset($_SESSION[$_POST['job-id']]['filename'])) {
            $_SESSION[$_POST['job-id']]['filename'] = md5(microtime(true)) . '.csv';
            // here we need to create file, because it is not exists yet
        }

        // and here we can store $data in the file with name $_SESSION[$_POST['job-id']]['filename']
    }

    /**
     * This method returns a subset of required data
     * For example SELECT * FROM your_table WHERE %condition% LIMIT %start_index%, $this->limit
     */
    protected function getDataBulk(int $from, int $limit): array
    {
        return [
            // some testing data
        ];
    }

	/**
	 *	This method calculates totoal amount of data
	 * for example SELECT COUNT(*) FROM your_table WHERE %condition%
	 */
    protected function getDataAmount(): int
    {
        // here we use testing data, but you can fetch it from database
        global $testData;

        return count($testData);
    }
}
```