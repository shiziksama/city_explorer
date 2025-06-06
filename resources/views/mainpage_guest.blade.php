@extends('index')

@section('head')
<title>City Explorer</title>
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 2rem;
    }
    .container {
        max-width: 800px;
        margin: auto;
    }
    h1 {
        margin-bottom: 1rem;
    }
    ul {
        list-style: none;
        padding-left: 0;
    }
    ul li::before {
        content: '\2714\0020';
        color: green;
    }
    .login-link {
        display: inline-block;
        margin-top: 2rem;
        padding: 0.5rem 1rem;
        background-color: #007bff;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <h1>City Explorer</h1>
    <p>Цей проект збирає ваші треки з різних фітнес‑сервісів і показує їх на карті.</p>
    <ul>
        <li>Імпорт даних зі Strava та MapMyFitness</li>
        <li>Зручний перегляд маршрутів на інтерактивній карті</li>
        <li>Мінімалістичний інтерфейс без зайвих налаштувань</li>
        <li>Відкритий код проекту</li>
    </ul>
    <a class="login-link" href="/login">Увійти</a>
</div>
@endsection
