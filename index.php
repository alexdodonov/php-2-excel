<?php

// some testing data
$testData = [
    [
        'id' => 1,
        'name' => 'Alex Smith'
    ],
    [
        'id' => 2,
        'name' => 'John Dow'
    ],
    [
        'id' => 3,
        'name' => 'Anna Garcia'
    ],
    [
        'id' => 4,
        'name' => 'Victor fon Doom'
    ],
    [
        'id' => 5,
        'name' => 'Ben Kingsley'
    ]
];

/**
 * Interface wich consists of all necessary methods
 *
 * @author gdever
 */
interface HttpJobInterface
{

    /**
     * Method runs job or starts another one
     */
    public function run(): void;
}

abstract class AbstractHttpJob implements HttpJobInterface
{

    /**
     * Method fetches some data to be processed
     *
     * @param int $from
     *            starting index of data
     * @param int $limit
     *            amount of data
     * @return array data to be processed
     */
    protected abstract function getDataBulk(int $from, int $limit): array;

    /**
     * Method process data
     *
     * @param array $data
     */
    protected abstract function process(array $data): void;

    /**
     * Method returns amount of data to be processed
     *
     * @return int amount of data
     */
    protected abstract function getDataAmount(): int;

    /**
     * Is job started
     *
     * @return bool true if the job was started, false otherwise
     */
    protected function isJobStarted(): bool
    {
        return isset($_SESSION[$_POST['job-id']]);
    }

    /**
     * Metehod sends response to the client
     *
     * @param int $processed
     *            amount of processed data
     */
    protected function sendResponse(int $processed): void
    {
        header('Content-Type: application/json');
        print('{"job-id": "' . $_SESSION[$_POST['job-id']]['job-id'] . '", "amount": "' . $this->getDataAmount() . '", "processed": "' . $processed . '"');

        // do we need to finish job
        if ($this->getDataAmount() === $processed) {
            print(', "filename" : "' . $_SESSION[$_POST['job-id']]['filename'] . '"');
        }

        print('}');
        die(0);
    }

    /**
     * Method starts job
     */
    protected function startJob(): void
    {
        header('Content-Type: application/json');
        print('{"job-id": "' . ($_SESSION[$_POST['job-id']]['job-id'] = md5(microtime(true))) . '", "amount": "' . $this->getDataAmount() . '", "processed": "0"}');
        die(0);
    }

    /**
     * Method starts new iteration of the job
     */
    protected function continueJob(): void
    {
        // get data portion
        $data = $this->getDataBulk($_POST['processed'], $this->limit);

        // process data portion
        $this->process($data);

        // send response
        $this->sendResponse(intval($_POST['processed']) + count($data));
    }

    /**
     *
     * {@inheritdoc}
     * @see HttpJobInterface::run()
     */
    public function run(): void
    {
        if (! $this->isJobStarted()) {
            $this->startJob();
        }

        $this->continueJob();
    }
}

class HttpJob implements HttpJobInterface
{

    /**
     * Amount of data to be processed per one iteration
     *
     * @var integer
     */
    protected $limit = 1;

    /**
     *
     * {@inheritdoc}
     * @see HttpJobInterface::process()
     */
    protected function process(array $data): void
    {
        if (! isset($_SESSION[$_POST['job-id']]['filename'])) {
            $_SESSION[$_POST['job-id']]['filename'] = md5(microtime(true)) . '.csv';
        }

        // and here we can store $data in the file with name $_SESSION[$_POST['job-id']]['filename']
    }

    /**
     *
     * {@inheritdoc}
     * @see HttpJobInterface::getDataBulk()
     */
    protected function getDataBulk(int $from, int $limit): array
    {
        // some testing data
        return [
            [
                'id' => 1,
                'amount' => '1111'
            ]
        ];
    }

    /**
     *
     * {@inheritdoc}
     * @see HttpJobInterface::getDataAmount()
     */
    protected function getDataAmount(): int
    {
        // here we use testing data, but you can fetch it from database
        global $testData;

        return count($testData);
    }
}

session_start();

$job = new HttpJob();
$job->run();