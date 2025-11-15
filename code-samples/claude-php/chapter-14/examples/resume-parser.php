<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\DocumentProcessing\DocumentProcessor;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Document Processing - Resume Parser\n\n";

$processor = new DocumentProcessor($_ENV['ANTHROPIC_API_KEY']);

$resumeText = <<<TEXT
JOHN DEVELOPER
Senior Software Engineer
john.developer@email.com | (555) 123-4567 | linkedin.com/in/johndev

EXPERIENCE

Senior Software Engineer | Tech Corp | 2020 - Present
- Led development of microservices architecture serving 10M+ users
- Reduced API response time by 40% through optimization
- Mentored team of 5 junior developers

Software Engineer | StartupXYZ | 2018 - 2020
- Built RESTful APIs using PHP and Laravel
- Implemented CI/CD pipeline with Docker and GitHub Actions

EDUCATION
BS Computer Science | State University | 2018

SKILLS
PHP, Laravel, MySQL, Docker, AWS, JavaScript, React
TEXT;

$prompt = 'Parse this resume and extract as JSON: name, email, phone, experience (array with company, role, dates, achievements), education, skills.';

echo "Parsing resume...\n\n";
$result = $processor->processText($resumeText, $prompt);

echo "Parsed resume data:\n";
echo $result['content'][0]['text'] . "\n";
