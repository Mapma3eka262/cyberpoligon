@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Точность прогнозов</h1>
            <p class="text-muted">Анализ качества прогнозирования системных метрик</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.forecasts.index') }}" class="btn btn-secondary">← Назад к прогнозам</a>
        </div>
    </div>

    @if($metrics->isEmpty())
        <div class="alert alert-warning">
            <strong>⚠️ Нет данных для оценки.</strong> 
            Сначала сгенерируйте прогнозы и дождитесь накопления фактических данных.
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h5>Метрики точности за последние 7 дней</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Метрика</th>
                                <th>Метод</th>
                                <th>Кол-во прогнозов</th>
                                <th>MAE</th>
                                <th>MAPE (%)</th>
                                <th>RMSE</th>
                                <th>Оценка</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($metrics as $metric)
                            <tr>
                                <td><strong>{{ $metric->metric_type }}</strong></td>
                                <td>{{ $metric->forecast_method }}</td>
                                <td>{{ $metric->predictions_count }}</td>
                                <td>{{ number_format($metric->avg_mae, 2) }}</td>
                                <td>
                                    <span class="badge {{ $metric->avg_mape < 10 ? 'bg-success' : ($metric->avg_mape < 30 ? 'bg-warning' : 'bg-danger') }}" style="font-size: 14px;">
                                        {{ number_format($metric->avg_mape, 1) }}%
                                    </span>
                                </td>
                                <td>{{ number_format($metric->avg_rmse, 2) }}</td>
                                <td>
                                    @if($metric->avg_mape < 10)
                                        ✓ Отлично
                                    @elseif($metric->avg_mape < 30)
                                        ⚠ Хорошо
                                    @else
                                        ✗ Требует улучшения
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>Рекомендации</h5>
            </div>
            <div class="card-body">
                @php
                    $bestMethods = [];
                    foreach($metrics as $metric) {
                        $bestMethods[$metric->metric_type] = $metric->forecast_method;
                    }
                @endphp
                
                <ul>
                    @foreach($bestMethods as $metric => $method)
                        <li><strong>{{ $metric }}</strong>: рекомендуется использовать метод <code>{{ $method }}</code></li>
                    @endforeach
                </ul>
                
                <div class="alert alert-info mt-3">
                    <strong>ℹ️ Интерпретация MAPE:</strong><br>
                    - &lt;10%: Отличная точность<br>
                    - 10-30%: Хорошая точность<br>
                    - &gt;30%: Низкая точность, требуется настройка модели
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
