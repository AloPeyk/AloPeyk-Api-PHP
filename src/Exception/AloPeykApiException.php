<?php

namespace AloPeyk\Exception;


use Throwable;

class AloPeykApiException extends \Exception
{
    /**
     * AloPeykApiException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Show Custom Message
     * @return string
     */
    public function errorMessage()
    {
        $errorMsg = "<div style='background:#f4d4d0; padding: 3px 30px 12px; border:1px solid #f2c5bf;'>";
        $errorMsg .= "<h1 style='color: #dd6252'>Alopeyk Exception:</h1>";
        $errorMsg .= "<h3>Error on:</h3>";
        $errorMsg .= "<p style='letter-spacing: 1px'><b>{$this->getFile()}</b> : line <b>{$this->getLine()}</b></p>";
        $errorMsg .= "<hr style='display: block;height: 1px;border: 0;border-top: 1px solid #e2b0aa;margin: 1em 0;padding: 0; '>";
        $errorMsg .= "<h3>Message:</h3>";
        $errorMsg .= "<p style='background-color: #e8b2ab; padding: 6px;'><b>{$this->getMessage()}</b></p>";
        $errorMsg .= "<h4>Stack Traces</h4>";

        $errorMsg .= "<table style='width: 100%; border: 1px solid #E8B2AB; font-size: 11px; color: #ce5244' border='1' cellspacing='0'>";
        $errorMsg .= "<thead style='font-size: 12px; letter-spacing: 1px; background: #E8B2AB; '>
                        <tr>
                        <th style='padding: 5px 10px; text-align: left;'>file</th>
                        <th style='padding: 5px 10px;'>line</th>
                        <th style='padding: 5px 10px; text-align: left'>function</th>
                        <th style='padding: 5px 10px; text-align: left'>class</th>
                        <!-- <th style='padding: 5px 10px;'>type</th>
                        <th style='padding: 5px 10px; text-align: left'>args</th> -->
                        </tr>
                      </thead>";
        $errorMsg .= "<tbody>";
        foreach ($this->getTrace() as $trace) {
            $errorMsg .= "<tr>";
            $errorMsg .= "<td>{$trace['file']}</td>";
            $errorMsg .= "<td style='text-align: center'>{$trace['line']}</td>";
            $errorMsg .= "<td>{$trace['function']}</td>";
            $errorMsg .= "<td>{$trace['class']}</td>";
//            $errorMsg .= "<td style='text-align: center'>{$trace['type']}</td>";
//            $errorMsg .= "<td><ol>";
//            foreach ($trace['args'] as $index => $arg) {
//                $errorMsg .= "<li>$arg</li>";
//            }
//            $errorMsg .= "</ol></td>";
//            $errorMsg .= "</ul></td>";
            $errorMsg .= "</tr>";
        }
        $errorMsg .= "</tbody>";
        $errorMsg .= "</table>";

        $errorMsg .= "</div>";

        echo "<pre>";
        die($errorMsg);
    }

    /**
     * Override __toString in parent
     */
    public function __toString()
    {
        $this->errorMessage();
    }
}