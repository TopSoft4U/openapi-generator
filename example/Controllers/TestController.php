<?php

class TestController
{
    /**
     * Description
     * @throws \Error400 Remember to put full qualified path.
     */
    public function GET_Info(string $variable, ?int $someNumber = null): TestResponse
    {
        if (!$variable)
            throw new Error400("This variable is required");

        $response = new TestResponse();
        $response->text = $variable;
        $response->number = $someNumber ?? 123;

        return $response;
    }

    /**
     * Description
     * Multiline even
     */
    public function POST_Data(TestPostRequest $requestBody): TestResponse
    {
        $response = new TestResponse();
        $response->text = $requestBody->text;
        $response->number = $requestBody->number;

        return $response;
    }
}