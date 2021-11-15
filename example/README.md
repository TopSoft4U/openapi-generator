This example does not provide any kind of routing!

Generated UI (index.php)
![image](https://user-images.githubusercontent.com/544349/141819565-88707ab2-3c93-4145-b1ef-e88edf6dfe8e.png)

Generated JSON:
```json
{
    "openapi": "3.0.0",
    "info": {
        "title": "Example OpenAPI document",
        "version": "0.1"
    },
    "servers": [
        {
            "url": "http://localhost"
        }
    ],
    "security": [
        {
            "User": []
        }
    ],
    "paths": {
        "/Test/Info": {
            "get": {
                "tags": [
                    "Test"
                ],
                "operationId": "GET/Test/Info",
                "parameters": [
                    {
                        "required": true,
                        "in": "query",
                        "name": "variable",
                        "schema": {
                            "type": "string"
                        }
                    },
                    {
                        "required": false,
                        "in": "query",
                        "name": "someNumber",
                        "schema": {
                            "type": "integer",
                            "nullable": true
                        }
                    }
                ],
                "responses": {
                    "200": {
                        "description": "Error 200",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/TestResponse"
                                }
                            }
                        }
                    },
                    "0": {
                        "description": "Remember to put full qualified path.",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Error400"
                                }
                            }
                        }
                    },
                    "400": {
                        "description": "Error 400",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Error400"
                                }
                            }
                        }
                    }
                }
            }
        },
        "/Test/Data": {
            "post": {
                "tags": [
                    "Test"
                ],
                "operationId": "POST/Test/Data",
                "requestBody": {
                    "required": true,
                    "content": {
                        "application/json": {
                            "schema": {
                                "$ref": "#/components/schemas/TestPostRequest"
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Error 200",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/TestResponse"
                                }
                            }
                        }
                    },
                    "400": {
                        "description": "Error 400",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Error400"
                                }
                            }
                        }
                    }
                }
            }
        }
    },
    "components": {
        "schemas": {
            "Error400": {
                "type": "object",
                "properties": {
                    "field": {
                        "type": "string"
                    },
                    "error": {
                        "type": "string"
                    }
                },
                "required": [
                    "field",
                    "error"
                ],
                "additionalProperties": false
            },
            "TestResponse": {
                "type": "object",
                "properties": {
                    "number": {
                        "type": "integer"
                    },
                    "text": {
                        "type": "string"
                    }
                },
                "required": [
                    "number",
                    "text"
                ],
                "additionalProperties": false
            },
            "TestPostRequest": {
                "type": "object",
                "properties": {
                    "number": {
                        "type": "integer"
                    },
                    "text": {
                        "type": "string"
                    },
                    "price": {
                        "type": "number",
                        "format": "float"
                    }
                },
                "required": [
                    "number",
                    "text",
                    "price"
                ],
                "additionalProperties": false
            }
        },
        "securitySchemes": {
            "User": {
                "type": "http",
                "scheme": "bearer"
            }
        }
    }
}
```
