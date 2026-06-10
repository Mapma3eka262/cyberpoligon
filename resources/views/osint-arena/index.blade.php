@extends('layouts.app')

@section('title', 'OSINT-арена')

@section('styles')
<style>
    .task-card {
        border-left: 4px solid #6f42c1;
    }
    .task-item {
        transition: transform 0.2s;
    }
    .task-item:hover {
        transform: translateX(5px);
    }
    .completed-badge {
        background-color: #28a745;
    }
    .active-badge {
        background-color: #ffc107;
        color: #000;
    }
    .expired-badge {
        background-color: #dc3545;
    }
</style>
@endsection

@section('content')
<div class="fade-in">
    <div class="row">
        <div class="col-lg-8">
            <div class="card task-card mb-4">
                <div class="task-card-header" style="background: linear-gradient(135deg, #6f42c1, #0d6efd);">
                    <h3 class="mb-0 text-white">
                        <i class="bi bi-search me-2"></i>OSINT-арена
                    </h3>
                    <p class="text-white-50 mb-0 mt-1">Поиск и анализ открытых источников</p>
                </div>
                <div class="task-card-body">
                    @if($userTasks->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-binoculars display-1 text-muted"></i>
                            <h4 class="mt-3">Нет активных OSINT-заданий</h4>
                            <p class="text-muted">Ожидайте назначения заданий от администратора</p>
                        </div>
                    @else
                        <div class="list-group">
                            @foreach($userTasks as $ut)
                                @php
                                    $isActive = !$ut->completed && (!$ut->end_time || now()->lt($ut->end_time));
                                    $statusClass = $ut->completed ? 'completed-badge' : ($isActive ? 'active-badge' : 'expired-badge');
                                    $statusText = $ut->completed ? 'Завершено' : ($isActive ? 'Активно' : 'Время истекло');
                                @endphp
                                <a href="{{ route('arena.osint.show', $ut) }}" 
                                   class="list-group-item list-group-item-action task-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">
                                                {{ $ut->task->name }}
                                                @if($ut->id === ($activeUserTask->id ?? null))
                                                    <span class="badge active-badge ms-2">Текущее</span>
                                                @endif
                                            </h5>
                                            <small class="text-muted">
                                                {{ Str::limit($ut->task->description ?? 'Нет описания', 100) }}
                                            </small>
                                            <div class="mt-2">
                                                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                                <span class="badge bg-secondary ms-1">
                                                    <i class="bi bi-star-fill"></i> {{ $ut->score }} баллов
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @if($ut->end_time && !$ut->completed)
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i>
                                                    До {{ $ut->end_time->format('d.m.Y H:i') }}
                                                </small>
                                            @endif
                                            <div class="mt-1">
                                                <i class="bi bi-chevron-right"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            @include('osint-arena.partials.rules')
        </div>
    </div>
</div>
@endsection