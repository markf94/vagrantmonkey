<?php
namespace ZfServerUrl;

use Zend\View\Helper\ServerUrl as BaseServerUrl;

class ServerUrl extends BaseServerUrl
{
    public function detectHost()
    {
        if ($this->setHostFromProxy()) {
            return;
        }

        if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
            // Detect if the port is set in SERVER_PORT and included in HTTP_HOST
            if (isset($_SERVER['SERVER_PORT'])
                && preg_match('/^(?P<host>.*?):(?P<port>\d+)$/', $_SERVER['HTTP_HOST'], $matches)
            ) {
                // If they are the same, set the host to just the hostname
                // portion of the Host header.
                if ((int) $matches['port'] === (int) $_SERVER['SERVER_PORT']) {
                    $this->setHost($matches['host']);
                    return;
                }

                // At this point, we have a SERVER_PORT that differs from the
                // Host header, indicating we likely have a port-forwarding
                // situation. As such, we'll set the host and port from the
                // matched values.
                $this->setPort((int) $matches['port']);
                $this->setHost($matches['host']);
                return;
            }

            $this->setHost($_SERVER['HTTP_HOST']);

            return;
        }

        if (!isset($_SERVER['SERVER_NAME']) || !isset($_SERVER['SERVER_PORT'])) {
            return;
        }

        $name = $_SERVER['SERVER_NAME'];
        $this->setHost($name);
    }
}
