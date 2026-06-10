@extends('layouts.app')

@section('title', $task->name . ' - OSINT-арена')

@section('styles')
<style>
    .question-card {
        border-left: 3px solid #6f42c1;
        margin-bottom: 20px;
        transition: all 0.2s;
    }
    .question-card.correct {
        border-left-color: #28a745;
        background-color: #f0fff4;
    }
    .question-card.incorrect {
        border-left-color: #dc3545;
    }
    .answer-input {
        font-family: 'Courier New', monospace;
    }
    .hint-btn {
        background: none;
        border: none;
        color: #ffc107;
        cursor: pointer;
    }
    .hint-text {
        background: #fff3cd;
        border-left: 3px solid #ffc107;
        padding: 10px;
        margin-top: 10px;
        font-size: 0.9rem;
    }
    .score-panel {
        background: linear-gradient(135deg, #6f42c1, #0d6efd);
        color: white;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
    }
    .source-badge {
        background: #e9ecef;
        color: #495057;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        margin-right: 5px;
        margin-bottom: 5px;
        display: inline-block;
    }
    .answer-history {
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@endsection

@section('content')
<div class="fade-in">
    <div class="row">
        <div class="col-lg-8">
            <!-- Информация о задании -->
            <div class="card task-card mb-4">
                <div class="task-card-header">
                    <h3 class="mb-0">{{ $task->name }}</h3>
                    @if($task->description)
                        <p class="text-muted mt-2 mb-0">{{ $task->description }}</p>
                    @endif
                </div>
                <div class="task-card-body">
                    <!-- Таймер -->
                    @if($timeRemaining && !$timeRemaining['is_expired'])
                        <div class="countdown-timer mb-4">
                            <div class="timer-label mb-2">Оставшееся время:</div>
                            <div id="task-countdown" 
                                 data-end-time="{{ $userTask->end_time ? $userTask->end_time->toISOString() : '' }}">
                                <span id="task-hours">{{ str_pad($timeRemaining['hours'], 2, '0', STR_PAD_LEFT) }}</span>:
                                <span id="task-minutes">{{ str_pad($timeRemaining['minutes'], 2, '0', STR_PAD_LEFT) }}</span>:
                                <span id="task-seconds">{{ str_pad($timeRemaining['seconds'], 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </div>
                    @elseif($userTask->end_time && $timeRemaining && $timeRemaining['is_expired'])
                        <div class="alert alert-danger mb-4">
                            <i class="bi bi-clock-history me-2"></i>
                            Время выполнения задания истекло. Ответы больше не принимаются.
                        </div>
                    @endif

                    <!-- Источники -->
                    @if(!empty($osintData['sources']))
                        <div class="mb-4">
                            <h5><i class="bi bi-database me-2"></i>Источники для анализа</h5>
                            <div class="mt-2">
                                @foreach($osintData['sources'] as $source)
                                    <span class="source-badge">
                                        @switch($source)
                                            @case('github') <i class="bi bi-github"></i> GitHub @break
                                            @case('twitter') <i class="bi bi-twitter-x"></i> Twitter @break
                                            @case('whois') <i class="bi bi-globe"></i> WHOIS @break
                                            @case('dns') <i class="bi bi-diagram-3"></i> DNS @break
                                            @case('shodan') <i class="bi bi-shield-shaded"></i> Shodan @break
                                            @case('google') <i class="bi bi-google"></i> Google Dorks @break
                                            @default <i class="bi bi-search"></i> {{ ucfirst($source) }}
                                        @endswitch
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Вопросы -->
                    <h5 class="mb-3"><i class="bi bi-question-circle me-2"></i>Вопросы</h5>
                    @foreach($osintData['expected_answers'] as $index => $question)
                        @php
                            $answer = $answers[$question['key']] ?? null;
                            $isCorrect = $answer && $answer->is_correct;
                            $questionPoints = $osintData['points_per_answer'][$index] ?? 10;
                        @endphp
                        <div class="question-card card {{ $isCorrect ? 'correct' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $question['description'] ?? 'Вопрос ' . ($index+1) }}</h6>
                                        <small class="text-muted">Ключ: {{ $question['key'] }} | Баллов: {{ $questionPoints }}</small>
                                    </div>
                                    @if($isCorrect)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Решено (+{{ $answer->points_awarded }})
                                        </span>
                                    @endif
                                </div>
                                
                                @if(!$isCorrect && (!$userTask->end_time || now()->lt($userTask->end_time)))
                                    <div class="mt-3">
                                        <form class="answer-form" data-key="{{ $question['key'] }}">
                                            @csrf
                                            <div class="input-group">
                                                <input type="text" class="form-control answer-input" 
                                                       name="answer" placeholder="Введите ответ..." required>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-send"></i> Проверить
                                                </button>
                                            </div>
                                        </form>
                                        <div class="answer-feedback mt-2 small"></div>
                                    </div>
                                @elseif($isCorrect)
                                    <div class="mt-2 text-success">
                                        <small><i class="bi bi-check-circle"></i> Ваш ответ: {{ $answer->user_answer }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <!-- Подсказки -->
                    @if(!empty($osintData['hints']) && (!$userTask->completed))
                        <div class="mt-4">
                            <h5><i class="bi bi-lightbulb me-2"></i>Подсказки (со штрафом)</h5>
                            <div class="row">
                                @foreach($osintData['hints'] as $index => $hint)
                                    @php $isUsed = in_array($index, $usedHints); @endphp
                                    <div class="col-md-6 mb-2">
                                        <button type="button" class="btn btn-sm btn-warning hint-request-btn w-100 text-start"
                                                data-hint-index="{{ $index }}"
                                                data-hint-cost="{{ $hint['cost'] }}"
                                                {{ $isUsed || ($userTask->end_time && now()->gt($userTask->end_time)) ? 'disabled' : '' }}>
                                            <i class="bi bi-lightbulb"></i> 
                                            Подсказка {{ $index+1 }} 
                                            <span class="badge bg-danger">-{{ $hint['cost'] }} баллов</span>
                                        </button>
                                        <div id="hint-result-{{ $index }}" class="hint-text d-none mt-1"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <!-- Счёт -->
            <div class="score-panel mb-4">
                <h4>Ваш счёт</h4>
                <h2 class="display-4" id="total-score">{{ $userTask->score }}</h2>
                <p class="mb-0">из {{ $osintData['max_score'] ?? 100 }} возможных</p>
            </div>

            <!-- История ответов -->
            <div class="card task-card mb-4">
                <div class="task-card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>История ответов</h5>
                </div>
                <div class="card-body answer-history">
                    @php
                        $history = \App\Models\OsintAnswer::where('task_id', $task->id)
                            ->where('user_id', auth()->id())
                            ->orderBy('created_at', 'desc')
                            ->get();
                    @endphp
                    @if($history->isEmpty())
                        <p class="text-muted text-center py-3">Нет отправленных ответов</p>
                    @else
                        @foreach($history as $record)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">{{ $record->question_key }}</small>
                                    <small>{{ $record->created_at->format('H:i:s') }}</small>
                                </div>
                                <div class="mt-1">
                                    <code class="small">{{ Str::limit($record->user_answer, 50) }}</code>
                                    @if($record->is_correct)
                                        <span class="badge bg-success float-end">+{{ $record->points_awarded }}</span>
                                    @else
                                        <span class="badge bg-danger float-end">✗</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            @include('osint-arena.partials.rules')
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Таймер
    const countdownElement = document.getElementById('task-countdown');
    if (countdownElement && countdownElement.dataset.endTime) {
        const endTime = new Date(countdownElement.dataset.endTime).getTime();
        const timerInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (distance < 0) {
                clearInterval(timerInterval);
                countdownElement.innerHTML = '<span class="text-danger">ВРЕМЯ ВЫШЛО!</span>';
                document.querySelectorAll('.answer-form button').forEach(btn => btn.disabled = true);
                document.querySelectorAll('.hint-request-btn').forEach(btn => btn.disabled = true);
                location.reload();
            } else {
                const hours = Math.floor(distance / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                document.getElementById('task-hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('task-minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('task-seconds').textContent = seconds.toString().padStart(2, '0');
            }
        }, 1000);
    }

    // Отправка ответов
    document.querySelectorAll('.answer-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const key = this.dataset.key;
            const answer = this.querySelector('input[name="answer"]').value.trim();
            const submitBtn = this.querySelector('button');
            const feedbackDiv = this.querySelector('.answer-feedback');
            
            if (!answer) {
                feedbackDiv.innerHTML = '<span class="text-danger">Введите ответ</span>';
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Проверка...';
            feedbackDiv.innerHTML = '';
            
            try {
                const response = await fetch('{{ route("arena.osint.answer", $userTask) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ key: key, answer: answer })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    feedbackDiv.innerHTML = '<span class="text-success">' + data.message + '</span>';
                    document.getElementById('total-score').textContent = data.total_score;
                    
                    if (data.completed) {
                        setTimeout(() => {
                            window.location.href = '{{ route("arena.osint") }}';
                        }, 2000);
                    } else if (data.question_key) {
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        form.closest('.question-card').classList.add('correct');
                        form.remove();
                    }
                } else {
                    feedbackDiv.innerHTML = '<span class="text-danger">' + data.message + '</span>';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-send"></i> Проверить';
                }
            } catch (error) {
                feedbackDiv.innerHTML = '<span class="text-danger">Ошибка сети</span>';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send"></i> Проверить';
            }
        });
    });

    // Подсказки
    document.querySelectorAll('.hint-request-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const hintIndex = this.dataset.hintIndex;
            const cost = this.dataset.hintCost;
            
            if (confirm(`⚠️ Использование подсказки будет стоить ${cost} баллов. Продолжить?`)) {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Загрузка...';
                
                try {
                    const response = await fetch('{{ route("arena.osint.hint", $userTask) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ hint_index: parseInt(hintIndex) })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        document.getElementById('total-score').textContent = data.new_score;
                        const hintDiv = document.getElementById('hint-result-' + hintIndex);
                        hintDiv.innerHTML = '<i class="bi bi-lightbulb"></i> ' + data.hint_text;
                        hintDiv.classList.remove('d-none');
                        this.innerHTML = '<i class="bi bi-check-circle"></i> Подсказка показана';
                    } else {
                        alert(data.message);
                        this.disabled = false;
                        this.innerHTML = '<i class="bi bi-lightbulb"></i> Подсказка ' + (parseInt(hintIndex)+1) + ' <span class="badge bg-danger">-' + cost + ' баллов</span>';
                    }
                } catch (error) {
                    alert('Ошибка сети');
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-lightbulb"></i> Подсказка ' + (parseInt(hintIndex)+1) + ' <span class="badge bg-danger">-' + cost + ' баллов</span>';
                }
            }
        });
    });
});
</script>
@endsection