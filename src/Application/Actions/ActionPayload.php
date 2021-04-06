<?php

declare(strict_types=1);

namespace App\Application\Actions;

use JsonSerializable;

class ActionPayload implements JsonSerializable {

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var bool
     */
    private $success;

    /**
     * @var bool
     */
    private $message;

    /**
     * @var array|object|null
     */
    private $data;

    /**
     * @var ActionError|null
     */
    private $error;

    /**
     * @param int                   $statusCode
     * @param array|object|null     $data
     * @param ActionError|null      $error
     */
    public function __construct(
            $data = null,
            string $message = "Exitoso",
            int $statusCode = 200,
            bool $success = true,
            ?ActionError $error = null
    ) {
        $this->statusCode = $statusCode;
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->error = $error;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }

    /**
     * @return array|null|object
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @return ActionError|null
     */
    public function getError(): ?ActionError {
        return $this->error;
    }

    /**
     * @return array
     */
    public function jsonSerialize() {
        $payload = [
            'statusCode' => $this->statusCode,
            'success' => $this->success,
            'message' => $this->message,
        ];

        if ($this->data !== null) {
            $payload['data'] = $this->data;
        } elseif ($this->error !== null) {
            $payload['error'] = $this->error;
        }

        return $payload;
    }

}
