<?php
function check_params($params)
{
    foreach ($params as $param) {
        if (!isset($_REQUEST[$param])) {
            $this->exit_msg("Missing parameter: $param");
        }
    }
}
