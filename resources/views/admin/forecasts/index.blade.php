@extends('layouts.app')

@section('title', 'Точность прогнозов')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Прогнозы метрик</h1>
            <p class="text-muted">Прогнозирование системных метрик на основе исторических данных</p>
        </div>
        <div class="col-auto">
            <form action="{{ route('admin.forecasts.generate') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="horizon" value="60">
                <button type="submit" class="btn btn-success">Сгенерировать прогнозы</button>
            </form>
            <form action="{{ route('admin.forecasts.evaluate') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-info">Оценить точность</button>
            </form>
        </div>
    </div>

    <div class="alert alert-info">
        <strong>ℹ️ Информация:</strong> Прогнозы генерируются на основе исторических данных за последние 2 часа. 
        Для работы модуля необходимо собрать минимум 6 точек метрик.
    </div>

    @if($forecasts->isEmpty())
        <div class="alert alert-warning">
            <strong>⚠️ Нет прогнозов.</strong> 
            Нажмите кнопку "Сгенерировать прогнозы" или дождитесь автоматической генерации.
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Метрика</th>
                                <th>Метод</th>
                                <th>Прогноз</th>
                                <th>На время</th>
                                <th>Горизонт</th>
                                <th>Статус</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($forecasts as $forecast)
                            <tr>
                                <td><strong>{{ $forecast->metric_type }}</strong></td>
                                <td>{{ $forecast->forecast_method }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ number_format($forecast->forecast_value, 1) }}%</span>
                                    @if($forecast->lower_bound && $forecast->upper_bound)
                                        <br><small class="text-muted">95% ДИ: {{ number_format($forecast->lower_bound, 1) }}% - {{ number_format($forecast->upper_bound, 1) }}%</small>
                                    @endif
                                </td>
                                <td>{{ $forecast->forecasted_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $forecast->horizon_minutes }} мин</td>
                                <td>
                                    @php
                                        $accuracy = $forecast->getLatestAccuracy();
                                    @endphp
                                    @if($accuracy)
                                        <span class="badge {{ $accuracy->percentage_error < 10 ? 'bg-success' : ($accuracy->percentage_error < 30 ? 'bg-warning' : 'bg-danger') }}">
                                            Ошибка: {{ number_format($accuracy->percentage_error, 1) }}%
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Ожидает оценки</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
		<div class="d-flex justify-content-center mt-4">
                    {{ $forecasts->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
