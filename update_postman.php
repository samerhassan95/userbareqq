<?php
$file = 'Bareqq_Complete_API.postman_collection.json';
$data = json_decode(file_get_contents($file), true);

$newFolder = [
    "name" => "Client Meetings",
    "item" => [
        [
            "name" => "List Meetings",
            "request" => [
                "method" => "GET",
                "header" => [
                    ["key" => "Authorization", "value" => "Bearer {{client_token}}"]
                ],
                "url" => [
                    "raw" => "{{base_url}}/api/client/meetings",
                    "host" => ["{{base_url}}"],
                    "path" => ["api", "client", "meetings"]
                ]
            ]
        ],
        [
            "name" => "Filter Meetings",
            "request" => [
                "method" => "GET",
                "header" => [
                    ["key" => "Authorization", "value" => "Bearer {{client_token}}"]
                ],
                "url" => [
                    "raw" => "{{base_url}}/api/client/meetings/filter?status=waiting",
                    "host" => ["{{base_url}}"],
                    "path" => ["api", "client", "meetings", "filter"],
                    "query" => [
                        ["key" => "status", "value" => "waiting"]
                    ]
                ]
            ]
        ],
        [
            "name" => "Join Meeting",
            "request" => [
                "method" => "GET",
                "header" => [
                    ["key" => "Authorization", "value" => "Bearer {{client_token}}"]
                ],
                "url" => [
                    "raw" => "{{base_url}}/api/client/meetings/1/join",
                    "host" => ["{{base_url}}"],
                    "path" => ["api", "client", "meetings", "1", "join"]
                ]
            ]
        ],
        [
            "name" => "Create Meeting",
            "request" => [
                "method" => "POST",
                "header" => [
                    ["key" => "Authorization", "value" => "Bearer {{client_token}}"],
                    ["key" => "Content-Type", "value" => "application/json"]
                ],
                "body" => [
                    "mode" => "raw",
                    "raw" => "{\n  \"date\": \"2026-06-15\",\n  \"start_time\": \"10:00\",\n  \"strategy_id\": 12,\n  \"meeting_name\": \"Project Kickoff\",\n  \"description\": \"Kickoff call\",\n  \"end_time\": \"11:00\"\n}",
                    "options" => [
                        "raw" => ["language" => "json"]
                    ]
                ],
                "url" => [
                    "raw" => "{{base_url}}/api/client/meetings",
                    "host" => ["{{base_url}}"],
                    "path" => ["api", "client", "meetings"]
                ]
            ]
        ],
        [
            "name" => "Delete Meeting",
            "request" => [
                "method" => "DELETE",
                "header" => [
                    ["key" => "Authorization", "value" => "Bearer {{client_token}}"]
                ],
                "url" => [
                    "raw" => "{{base_url}}/api/client/meetings/1",
                    "host" => ["{{base_url}}"],
                    "path" => ["api", "client", "meetings", "1"]
                ]
            ]
        ],
        [
            "name" => "Available Slots",
            "request" => [
                "method" => "GET",
                "header" => [
                    ["key" => "Authorization", "value" => "Bearer {{client_token}}"]
                ],
                "url" => [
                    "raw" => "{{base_url}}/api/client/available-slots?date=2026-06-15",
                    "host" => ["{{base_url}}"],
                    "path" => ["api", "client", "available-slots"],
                    "query" => [
                        ["key" => "date", "value" => "2026-06-15"]
                    ]
                ]
            ]
        ],
        [
            "name" => "Unbooked Slots",
            "request" => [
                "method" => "GET",
                "header" => [
                    ["key" => "Authorization", "value" => "Bearer {{client_token}}"]
                ],
                "url" => [
                    "raw" => "{{base_url}}/api/client/unbooked-slots",
                    "host" => ["{{base_url}}"],
                    "path" => ["api", "client", "unbooked-slots"]
                ]
            ]
        ]
    ]
];

$data['item'][] = $newFolder;

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Postman collection updated successfully.\n";
