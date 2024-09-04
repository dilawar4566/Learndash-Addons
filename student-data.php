<?php
header('Content-Type: application/json');

// Get student ID from request
$studentId = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Sample data; replace with actual data source
$data = [
    1 => [
        'labels' => ['Writing', 'Reading', 'Overall'],
        'datasets' => [
            [
                'label' => 'Student 1 Skills',
                'data' => [75, 85, 80],
                'backgroundColor' => [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                'test' => [
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                'hello' => 1
            ]
        ]
    ],
    2 => [
        'labels' => ['Writing', 'Reading', 'Overall'],
        'datasets' => [
            [
                'label' => 'Student 2 Skills',
                'data' => [65, 75, 70],
                'backgroundColor' => [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                'borderColor' => [
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                'borderWidth' => 1
            ]
        ]
    ]
];

// Output data for the requested student ID
echo json_encode($data[$studentId]);