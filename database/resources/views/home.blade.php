@extends('layouts.app')

@section('title', 'Главная страница')

@section('content')
<div class="fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Заголовок события -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-danger mb-3">CTF Соревнования</h1>
                <p class="lead text-muted">
                    Захват флага — это соревнование по компьютерной безопасности, где участники 
                    решают задачи для поиска скрытых флагов и демонстрации своих навыков.
                </p>
            </div>

            <!-- Таймер обратного отсчета -->
            <div class="countdown-timer mb-5">
                <div class="timer-label mb-2">До начала соревнования:</div>
                <div id="countdown" data-countdown="{{ $competitionStart }}">
                    @if($timeRemaining['has_started'])
                        <span class="text-success">СОРЕВНОВАНИЕ НАЧАЛОСЬ!</span>
                    @else
                        <span id="days">{{ $timeRemaining['days'] }}</span>
                        <span id="hours">{{ str_pad($timeRemaining['hours'], 2, '0', STR_PAD_LEFT) }}</span>:
                        <span id="minutes">{{ str_pad($timeRemaining['minutes'], 2, '0', STR_PAD_LEFT) }}</span>:
                        <span id="seconds">{{ str_pad($timeRemaining['seconds'], 2, '0', STR_PAD_LEFT) }}</span>
                    @endif
                </div>
            </div>

            <!-- Информация о событии -->
            <div class="card task-card mb-5">
                <div class="task-card-header">
                    <h3 class="mb-0">О событии</h3>
                </div>
                <div class="task-card-body">
                    <p>Присоединяйтесь к нашим ежегодным киберучениям в формате CTF-соревнований для студентов, где команды соревнуются 
                    в решении сложных задач по кибербезопасности.</p>
                    
                    <h5>Формат проведения:</h5>
                    <ul>
                        <li><strong>Командный зачет</strong> — Все участники играют в составе команд. Учитывается общий результат команды в рейтинге</li>
                        <li><strong>До 5 человек в команде</strong> — В каждой команде допускается от 1 до 5 участников</li>
                        <li><strong>Формат флага</strong> — Все флаги соответствуют регулярному выражению CTF{[A-Za-z0-9_]+}</li>
                    </ul>
                    <h5>Во время соревнования запрещается:</h5>
                    <ul>
                        <li><strong>Обмениваться флагами</strong> — Запрещена передача флагов между командами. Нарушители могут быть дисквалифицированы по решению организаторов</li>
                        <li><strong>Атаковать организаторов</strong> — Запрещено атаковать платформу и иные сервисы организаторов</li>
                        <li><strong>Мешать участникам</strong> — Запрещено сознательно мешать другим: перегружать сервисы, флудить или эксплуатировать уязвимости вне представленных задач</li>
                    </ul>
                </div>
            </div>

            <!-- Кнопка участия -->
            <div class="text-center">
                @guest
                    <a href="{{ route('login.register') }}" class="btn btn-ctf btn-lg px-5 py-3">
                        <i class="bi bi-flag-fill me-2"></i>Участвовать в соревнованиях
                    </a>
                @else
                    <a href="{{ route('arena') }}" class="btn btn-ctf-success btn-lg px-5 py-3">
                        <i class="bi bi-play-fill me-2"></i>Перейти на арену
                    </a>
                @endguest
            </div>
        </div>
    </div>

    <!-- Партнеры 
    <div class="row mt-5 pt-5 border-top">
        <div class="col-12">
            <h4 class="text-center mb-4">Наши партнеры</h4>
            <div class="row justify-content-center">
                <div class="col-auto">
                    <div class="bg-danger rounded p-4 text-center">
                        <h5 class="text-color">СКАНБИТ</h5>
                        <p class="small mb-0">Организатор соревнований</p>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="bg-danger rounded p-4 text-center">
                        <h5 class="text-color">Департамент информационных технологий и цифрового развития Курганской области</h5>
                        <p class="small mb-0">Организационный партнер</p>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="bg-danger rounded p-4 text-center">
                        <h5 class="text-color">АйТи Новация</h5>
                        <p class="small mb-0">Технический партнер</p>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Контактная информация -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <p class="text-muted">
                Контактная информация: v.kofanov@scanbit.ru | +7 (991) 466-42-01<br>
                Адрес: г. Курган, ул. Гоголя, д. 44, помещение 11, офис 205
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(!$timeRemaining['has_started'])
    // Инициализация таймера
    CTFPlatform.initCountdownTimers();
    @endif
});
</script>
@endsection
