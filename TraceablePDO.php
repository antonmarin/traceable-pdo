<?php

namespace traceablePdo;

/**
 * PDO подключение к базе
 *
 * Добавляет трейс к запросу
 */
class TraceablePDO extends \PDO
{
    /** @var int Depth of trace. Disabled if false */
    public $traceLevel = false;

    /**
     * {@inheritdoc}
     *
     * @internal Overrided to add trace
     */
    public function prepare($statement, array $driver_options = array())
    {
        $trace = $this->collectTrace();
        $trace = empty($trace) ? '' : "\n    " . implode("\n    ", $trace);
        $statement .= '/* ' . base64_encode(gzcompress($trace)) . ' */';
        return parent::prepare($statement, $driver_options);
    }

    /**
     * get trace of statement
     * @return array Массив строк с указанием на строку файла
     */
    private function collectTrace()
    {
        $traces = [];
        $count = 0;
        $ts = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($ts); // first is ->prepare() it is not useful
        array_pop($ts); // remove the last trace since it would be the entry script, not very useful
        foreach ($ts as $trace) {
            if (
                isset($trace['file'], $trace['line'])
                && (!defined('YII2_PATH') || strpos($trace['file'], YII2_PATH) !== 0)
            ) {
                $traces[] = "#$count {$trace['file']}:{$trace['line']}";
                if ($this->traceLevel !== false && ++$count >= $this->traceLevel) {
                    break;
                }
            }
        }

        return $traces;
    }
}
