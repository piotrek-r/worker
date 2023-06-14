# Worker #

A simple worker implementation to loop a callback function until one of the defined conditions is met

## Installation ##

```bash
composer require piotrek-r/worker
```

## Usage ##

### Basic usage ###

```php
$worker = new \PiotrekR\Worker\Worker();

$worker->run(function () {
    // do something
});
```

The callback function should return a truthy value if it did some work, and a falsy value otherwise. This is used to determine stop conditions and sleep time.

### Conditions ###

`WorkerConditions` can be used to set conditions for the worker to stop.

#### Condition: time limit ####

You can set a time limit in seconds.

```php
$workerConditions = new \PiotrekR\Worker\WorkerConditions(
    timeSeconds: 10,
);

$worker = new \PiotrekR\Worker\Worker($workerConditions);

$worker->run(function () {
    // do something
});
```

#### Condition: memory limit ####

You can set a memory limit in bytes as int or as a string with a unit suffix. Supported units are: `b`, `k`, `m`, `g`, `t`, `p`, `e`, `z`, `y`. It uses `memory_get_usage(true)` to determine the current memory usage.

```php
$workerConditions = new \PiotrekR\Worker\WorkerConditions(
    memoryLimit: '512m',
);

$worker = new \PiotrekR\Worker\Worker($workerConditions);

$worker->run(function () {
    // do something
});
```

#### Condition: loop limit ####

You can set a loop limit. It will stop the worker after the given number of loops.

There are three different types of loop limits:

* `countLoops` - the number of loops which is the sum of the other types
* `countHandled` - the number of loops were the function returned a truthy value
* `countEmpty` - the number of loops were the function returned a falsy value

To only make 100 loops no matter the result:

```php
$workerConditions = new \PiotrekR\Worker\WorkerConditions(
    countLoops: 100,
);
```

To only run 5 times when the function returns `true` and loop infinitely when it returns `false`:

```php
$workerConditions = new \PiotrekR\Worker\WorkerConditions(
    countHandled: 5,
);
```

To run infinitely and stop after the first time the function returns `false` (e.g. when draining a queue, it will run until the queue is empty):

```php
$workerConditions = new \PiotrekR\Worker\WorkerConditions(
    countEmpty: 1,
);
```

### Configuration ###

#### Sleep time ####

You can set the sleep time in microseconds. It will sleep after each loop. You can set separate values for sleep time after a loop with a truthy value and a falsy value.

```php
$workerConfiguration = new \PiotrekR\Worker\WorkerConfiguration(
    sleepMicrosecondsAfterHandled: 1000, // 1ms to get quick to the next item
    sleepMicrosecondsAfterEmpty: 5000000, // 5s to not spam the queue
);

$worker = new \PiotrekR\Worker\Worker(null, $workerConfiguration);

$worker->run(function () {
    // do something
});
```

### Arguments ###

You can pass arguments to the `run` method. All arguments will be passed to the callback function.

```php
$arg1 = 'foo';
$arg2 = 'bar';
$arg3 = 3;

$worker = new \PiotrekR\Worker\Worker();

$worker->run(function (string $arg1, string $arg2, int $arg3) {
    echo $argument;
    return true;
}, $arg1, $arg2, $arg3);
```

### Result ###

The `run` method returns a `WorkerResult` object with the following methods:

* `getTimeElapsed(): int` - the time elapsed in seconds
* `getCountLoops(): int` - the number of loops which is the sum of the other types
* `getCountHandled(): int` - the number of loops were the function returned a truthy value
* `getCountEmpty(): int` - the number of loops were the function returned a falsy value

### Example ###

```php
use PiotrekR\Worker\Worker;
use PiotrekR\Worker\WorkerConditions;
use PiotrekR\Worker\WorkerConfiguration;

$queue = new ThirdPartyQueueHandler();

$workerConditions = new WorkerConditions(
    timeSeconds: 10,
    memory: '512m',
);

$workerConfiguration = new WorkerConfiguration(
    sleepMicrosecondsAfterHandled: 1000,
    sleepMicrosecondsAfterEmpty: 1000000,
);

$worker = new Worker($workerConditions, $workerConfiguration);

$result = $worker->run(function (ThirdPartyQueueHandler $queue) {
    return $queue->handleNextMessage();
}, $queue);

printf(
    "It took %d seconds to loop %d times. Of which %d were handled, and %d were empty.\n",
    $result->getTimeElapsed(),
    $result->getCountLoops(),
    $result->getCountHandled(),
    $result->getCountEmpty(),
);
```
