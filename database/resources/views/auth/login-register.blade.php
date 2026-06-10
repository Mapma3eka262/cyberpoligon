@extends('layouts.app')

@section('title', 'Вход и регистрация')

@section('styles')
<style>
    .tab-content {
        min-height: 500px;
    }
    .form-check-input:checked {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="form-ctf">
            <!-- Вкладки -->
            <ul class="nav nav-tabs-ctf nav-fill mb-4" id="authTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="login-tab" data-bs-toggle="tab" 
                            data-bs-target="#login" type="button" role="tab">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Вход
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="register-tab" data-bs-toggle="tab" 
                            data-bs-target="#register" type="button" role="tab">
                        <i class="bi bi-person-plus me-2"></i>Регистрация
                    </button>
                </li>
            </ul>

            <!-- Контент вкладок -->
            <div class="tab-content" id="authTabsContent">
                <!-- Вход -->
                <div class="tab-pane fade show active" id="login" role="tabpanel">
                    <form method="POST" action="{{ route('login') }}" data-validate-form>
                        @csrf
                        
                        <div class="mb-3">
                            <label for="login-username" class="form-label">Логин</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="login-username" name="username" required 
                                   value="{{ old('username') }}">
                            @error('username')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="login-password" class="form-label">Пароль</label>
                            <input type="password" class="form-control form-control-ctf" 
                                   id="login-password" name="password" required 
                                   data-min-length="6">
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-ctf">
                                <i class="bi bi-unlock me-2"></i>Войти
                            </button>
                        </div>
                        
                        @if(session('error'))
                            <div class="alert alert-danger mt-3">
                                {{ session('error') }}
                            </div>
                        @endif
                    </form>
                </div>

                <!-- Регистрация -->
                <div class="tab-pane fade" id="register" role="tabpanel">
                    <form method="POST" action="{{ route('register') }}" data-validate-form>
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="register-surname" class="form-label">Фамилия *</label>
                                <input type="text" class="form-control form-control-ctf" 
                                       id="register-surname" name="surname" required 
                                       value="{{ old('surname') }}">
                                @error('surname')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="register-name" class="form-label">Имя *</label>
                                <input type="text" class="form-control form-control-ctf" 
                                       id="register-name" name="name" required 
                                       value="{{ old('name') }}">
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="register-patronymic" class="form-label">Отчество</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="register-patronymic" name="patronymic" 
                                   value="{{ old('patronymic') }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="register-username" class="form-label">Логин *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="register-username" name="username" required 
                                   value="{{ old('username') }}">
                            <div class="form-text">Минимум 3 символа</div>
                            @error('username')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="register-email" class="form-label">Email *</label>
                            <input type="email" class="form-control form-control-ctf" 
                                   id="register-email" name="email" required 
                                   value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="register-phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control form-control-ctf" 
                                   id="register-phone" name="phone" 
                                   value="{{ old('phone') }}">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="register-password" class="form-label">Пароль *</label>
                                <input type="password" class="form-control form-control-ctf" 
                                       id="register-password" name="password" required 
                                       data-min-length="6">
                                <div class="form-text">Минимум 6 символов</div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="register-password-confirmation" class="form-label">Подтверждение пароля *</label>
                                <input type="password" class="form-control form-control-ctf" 
                                       id="register-password-confirmation" 
                                       name="password_confirmation" required>
                            </div>
                        </div>
                        
                        <!-- Выбор роли и названия команды -->
                        <div class="mb-3">
                            <label class="form-label">Роль в команде *</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="role" 
                                       id="role-captain" value="captain" 
                                       {{ old('role') == 'captain' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="role-captain">
                                    Капитан (создаст новую команду)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" 
                                       id="role-participant" value="participant" 
                                       {{ old('role') == 'participant' ? 'checked' : '' }}>
                                <label class="form-check-label" for="role-participant">
                                    Участник (присоединится к команде позже)
                                </label>
                            </div>
                            @error('role')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3" id="team-name-field" style="display: none;">
                            <label for="register-team-name" class="form-label">Название команды *</label>
                            <input type="text" class="form-control form-control-ctf" 
                                   id="register-team-name" name="team_name" 
                                   value="{{ old('team_name') }}">
                            @error('team_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Согласие на обработку ПД -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       id="agree-pd" name="agree_pd" 
                                       data-required-checkbox 
                                       {{ old('agree_pd') ? 'checked' : '' }}>
                                <label class="form-check-label" for="agree-pd">
                                    Я согласен на обработку 
                                    <a href="/privacy" target="_blank">персональных данных</a> *
                                </label>
                            </div>
                            @error('agree_pd')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-ctf-success" disabled>
                                <i class="bi bi-person-check me-2"></i>Зарегистрироваться
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация валидации форм
    CTFPlatform.initFormValidation();
    
    // Показать/скрыть поле названия команды
    const captainRadio = document.getElementById('role-captain');
    const teamNameField = document.getElementById('team-name-field');
    const teamNameInput = document.getElementById('register-team-name');
    
    function toggleTeamNameField() {
        if (captainRadio.checked) {
            teamNameField.style.display = 'block';
            teamNameInput.required = true;
        } else {
            teamNameField.style.display = 'none';
            teamNameInput.required = false;
        }
    }
    
    captainRadio.addEventListener('change', toggleTeamNameField);
    document.getElementById('role-participant').addEventListener('change', toggleTeamNameField);
    
    // Инициализация при загрузке
    toggleTeamNameField();
    
    // Обработка вкладок Bootstrap
    const authTabs = document.getElementById('authTabs');
    if (authTabs) {
        const tabButtons = authTabs.querySelectorAll('button[data-bs-toggle="tab"]');
        tabButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Обновляем URL без перезагрузки страницы
                const tabId = this.getAttribute('id').replace('-tab', '');
                history.replaceState(null, null, '?tab=' + tabId);
            });
        });
        
        // Восстанавливаем активную вкладку из URL
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        if (activeTab) {
            const tabButton = document.getElementById(activeTab + '-tab');
            if (tabButton) {
                const tabInstance = new bootstrap.Tab(tabButton);
                tabInstance.show();
            }
        }
    }
});
</script>
@endsection