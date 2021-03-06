<?php

namespace antonmarin\TraceablePDO;

/**
 * PDO подключение к базе.
 *
 * Добавляет трейс к запросу
 */
class PDO extends \PDO
{
    /** @var bool|int Depth of trace. Disabled if false */
    public $traceLevel = false;

    /**
     * {@inheritdoc}
     *
     * @internal Overrided to add trace
     * @noinspection PhpSignatureMismatchDuringInheritanceInspection
     */
    public function prepare($statement, $options = [])
    {
        $trace = $this->getTrace();
        $trace = $this->cutTrace($trace);
        $trace = $this->formatTrace($trace);
        $statement .= $this->comment($trace);

        return parent::prepare($statement, $options);
    }

    /**
     * Get trace of statement source without internal routes.
     *
     * @return array [file, line, function, class, type]
     */
    protected function getTrace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $trace = array_filter(
            $trace,
            static function ($row) {
                return !isset($row['file']) || __FILE__ !== $row['file'];
            }
        );

        return $trace;
    }

    /**
     * Cut trace to match {@link traceLevel}.
     *
     * @return array
     */
    protected function cutTrace(array $trace)
    {
        if (false !== $this->traceLevel) {
            $trace = array_splice($trace, 0, $this->traceLevel);
        }

        return $trace;
    }

    /**
     * Format trace to readable string.
     *
     * @param array $trace trace from {@link getTrace}
     *
     * @return string
     */
    protected function formatTrace(array $trace)
    {
        $traceStrings = [];
        foreach ($trace as $key => $row) {
            if (isset($row['file'], $row['line'])) {
                $traceStrings[] .= sprintf('#%d %s:%d', $key, $row['file'], $row['line']);
            }
        }

        return implode("\n", $traceStrings);
    }

    /**
     * Make string a comment to securely add to statement.
     *
     * @param string $string
     *
     * @return string
     */
    protected function comment($string)
    {
        return ' /* '.$this->encode($string).' */ ';
    }

    /**
     * Encode string to make it short string.
     *
     * @param string $string
     *
     * @return string string encoded with base64_encode(gzcompress($string))
     */
    protected function encode($string)
    {
        return base64_encode(gzcompress($string));
    }
}
