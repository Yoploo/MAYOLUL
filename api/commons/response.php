<?php
class Response
{
    private $code;
    private $content;

    function __construct()
    {
    }


    function send(): void
    {

        http_response_code($this->code);
        header("Content-Type: application/json; charset=utf8");
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS,PATCH');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        echo $this->content;
        exit();
    }


    /**
     * @param mixed $code
     */
    function setCode($code): void
    {
        $this->code = $code;
    }

    /**
     * @param mixed $content
     * @param int $code
     */
    function setContent($content, $code = 200): void
    {
        $this->content = json_encode($content, JSON_UNESCAPED_SLASHES);
        $this->code = $code;
    }

    /**
     * @param string $message
     * @param int $code
     */
    function setMessage($message, $code = 201): void
    {
        $this->content = '{ "message": "' . $message . '"}';
        $this->code = $code;
    }

    function setElement($message, $element): void
    {
        $existingContent = json_decode($this->content, true);
        $newContent = [$element => $message];
        $this->content = json_encode(array_merge($existingContent, $newContent), JSON_UNESCAPED_SLASHES);
    }

    public static function apartmentToArray(ApartmentModel $apartment)
    {
        return [
            [
                'name' => $apartment->name,
                'desc' => $apartment->description,
                'disponibility' => $apartment->disponibility,
                'address' => $apartment->address
            ],
            [
                ['area' => $apartment->area],
                ['capacity' => $apartment->capacity],
                ['price' => $apartment->price]
            ]
        ];
    }
}
