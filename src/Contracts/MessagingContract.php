<?php

namespace Dan\Contracts;

interface MessagingContract
{
    /**
     * Sends a message.
     *
     * @param $message
     * @param array $styles
     */
    public function message($message, $styles = []);

    /**
     * Sends an action.
     *
     * @param $message
     */
    public function action($message);

    /**
     * Sends a notice.
     *
     * @param $message
     */
    public function notice($message);
}
