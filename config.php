<?php
const PRODUCTION = false;

if(!PRODUCTION){
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

const EVO_SERVER_URL = 'http://localhost:8080';

const EVO_WEBHOOK = 'http://localhost/EvolutionAPI/webhook';


const GROQ_MODEL = 'llama-3.1-70b-versatile';
const GROQ_SYSTEM_PROMPT = 'Você é é um ótimo assistente de IA'; // you can give instruction on how the model should respond

const GROQ_ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

#DATABASE CONFIG
const DB_CONFIG = [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => "3306",
    "dbname" => 'notify',
    "username" => 'crud',
    "password" => 'love',
    "options" => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
];

