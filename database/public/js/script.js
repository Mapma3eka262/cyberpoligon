/**
 * Основной JavaScript файл для CTF платформы
 * Адаптировано из cyberpolygon
 */

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех компонентов
    initTabs();
    initCountdownTimers();
    initFormValidation();
    initDynamicForms();
});

/**
 * Инициализация системы вкладок
 */
function initTabs() {
    const tabButtons = document.querySelectorAll('[data-tab-toggle]');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetTab = this.getAttribute('data-tab-target');
            const tabContainer = this.closest('[data-tabs-container]');
            
            if (!tabContainer) return;
            
            // Скрыть все вкладки
            const allTabs = tabContainer.querySelectorAll('[data-tab-content]');
            allTabs.forEach(tab => {
                tab.classList.remove('active');
                tab.style.display = 'none';
            });
            
            // Показать целевую вкладку
            const targetTabContent = tabContainer.querySelector(`[data-tab-content="${targetTab}"]`);
            if (targetTabContent) {
                targetTabContent.classList.add('active');
                targetTabContent.style.display = 'block';
            }
            
            // Обновить активную кнопку
            const allButtons = tabContainer.querySelectorAll('[data-tab-toggle]');
            allButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

/**
 * Инициализация таймеров обратного отсчета
 */
function initCountdownTimers() {
    const timers = document.querySelectorAll('[data-countdown]');
    
    timers.forEach(timerElement => {
        const endTime = new Date(timerElement.getAttribute('data-countdown')).getTime();
        
        function updateTimer() {
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (distance < 0) {
                timerElement.innerHTML = '<span class="text-danger">ВРЕМЯ ВЫШЛО!</span>';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            let timerHTML = '';
            if (days > 0) {
                timerHTML += `<span>${days}d </span>`;
            }
            timerHTML += `
                <span>${hours.toString().padStart(2, '0')}:</span>
                <span>${minutes.toString().padStart(2, '0')}:</span>
                <span>${seconds.toString().padStart(2, '0')}</span>
            `;
            
            timerElement.innerHTML = timerHTML;
            
            // Обновлять каждую секунду
            setTimeout(updateTimer, 1000);
        }
        
        updateTimer();
    });
}

/**
 * Инициализация валидации форм
 */
function initFormValidation() {
    const forms = document.querySelectorAll('[data-validate-form]');
    
    forms.forEach(form => {
        const submitButton = form.querySelector('[type="submit"]');
        const requiredCheckbox = form.querySelector('[data-required-checkbox]');
        
        if (requiredCheckbox && submitButton) {
            requiredCheckbox.addEventListener('change', function() {
                submitButton.disabled = !this.checked;
                if (!this.checked) {
                    submitButton.classList.add('disabled');
                } else {
                    submitButton.classList.remove('disabled');
                }
            });
            
            // Инициализируем состояние кнопки
            submitButton.disabled = !requiredCheckbox.checked;
            if (!requiredCheckbox.checked) {
                submitButton.classList.add('disabled');
            }
        }
        
        // Валидация email
        const emailInputs = form.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            input.addEventListener('blur', function() {
                const email = this.value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    this.classList.add('is-invalid');
                    showValidationError(this, 'Введите корректный email адрес');
                } else {
                    this.classList.remove('is-invalid');
                    removeValidationError(this);
                }
            });
        });
        
        // Валидация пароля
        const passwordInputs = form.querySelectorAll('input[type="password"]');
        passwordInputs.forEach(input => {
            if (input.hasAttribute('data-min-length')) {
                const minLength = parseInt(input.getAttribute('data-min-length'));
                
                input.addEventListener('blur', function() {
                    if (this.value && this.value.length < minLength) {
                        this.classList.add('is-invalid');
                        showValidationError(this, `Пароль должен быть не менее ${minLength} символов`);
                    } else {
                        this.classList.remove('is-invalid');
                        removeValidationError(this);
                    }
                });
            }
        });
    });
}

/**
 * Показать сообщение об ошибке валидации
 */
function showValidationError(input, message) {
    let errorElement = input.nextElementSibling;
    
    if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
        errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        input.parentNode.insertBefore(errorElement, input.nextSibling);
    }
    
    errorElement.textContent = message;
}

/**
 * Удалить сообщение об ошибке валидации
 */
function removeValidationError(input) {
    const errorElement = input.nextElementSibling;
    if (errorElement && errorElement.classList.contains('invalid-feedback')) {
        errorElement.remove();
    }
}

/**
 * Инициализация динамических форм (добавление участников и т.д.)
 */
function initDynamicForms() {
    const addMemberButton = document.querySelector('[data-add-member]');
    
    if (addMemberButton) {
        addMemberButton.addEventListener('click', function() {
            const membersContainer = document.querySelector('[data-members-container]');
            const memberTemplate = document.querySelector('[data-member-template]');
            
            if (!membersContainer || !memberTemplate) return;
            
            const newMember = memberTemplate.cloneNode(true);
            newMember.style.display = 'block';
            newMember.removeAttribute('data-member-template');
            
            // Увеличиваем индекс
            const totalMembers = membersContainer.querySelectorAll('[data-member-index]').length;
            newMember.setAttribute('data-member-index', totalMembers);
            
            // Обновляем имена полей
            const inputs = newMember.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace('[0]', `[${totalMembers}]`));
                }
                
                const id = input.getAttribute('id');
                if (id) {
                    input.setAttribute('id', id.replace('-0-', `-${totalMembers}-`));
                }
            });
            
            // Добавляем кнопку удаления
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-sm btn-danger mt-2';
            removeButton.innerHTML = '<i class="bi bi-trash"></i> Удалить';
            removeButton.addEventListener('click', function() {
                newMember.remove();
                updateMembersIndexes();
            });
            
            newMember.querySelector('.member-fields').appendChild(removeButton);
            membersContainer.appendChild(newMember);
        });
    }
}

/**
 * Обновить индексы участников после удаления
 */
function updateMembersIndexes() {
    const membersContainer = document.querySelector('[data-members-container]');
    if (!membersContainer) return;
    
    const members = membersContainer.querySelectorAll('[data-member-index]');
    
    members.forEach((member, index) => {
        member.setAttribute('data-member-index', index);
        
        const inputs = member.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                const newName = name.replace(/\[\d+\]/, `[${index}]`);
                input.setAttribute('name', newName);
            }
            
            const id = input.getAttribute('id');
            if (id) {
                const newId = id.replace(/-\d+-/, `-${index}-`);
                input.setAttribute('id', newId);
            }
        });
    });
}

/**
 * Показать уведомление
 */
function showNotification(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Добавляем уведомление в начало контейнера
    const container = document.querySelector('.container');
    if (container) {
        const firstChild = container.firstChild;
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Автоматически скрыть через 5 секунд
        setTimeout(() => {
            const alert = document.querySelector('.notification-alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}

/**
 * Отправка формы через AJAX
 */
function submitFormAjax(form, options = {}) {
    return new Promise((resolve, reject) => {
        const formData = new FormData(form);
        const url = form.getAttribute('action') || window.location.href;
        const method = form.getAttribute('method') || 'POST';
        
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        
        // Устанавливаем заголовок для CSRF токена Laravel
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        if (token) {
            xhr.setRequestHeader('X-CSRF-TOKEN', token);
        }
        
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                    
                    if (response.success && options.successMessage) {
                        showNotification(options.successMessage, 'success');
                    }
                    
                    if (response.redirect) {
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, options.redirectDelay || 1000);
                    }
                } catch (e) {
                    reject(new Error('Invalid JSON response'));
                }
            } else {
                reject(new Error(`Request failed with status ${xhr.status}`));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        xhr.send(formData);
    });
}

// Экспортируем функции для использования в других файлах
window.CTFPlatform = {
    initTabs,
    initCountdownTimers,
    initFormValidation,
    initDynamicForms,
    showNotification,
    submitFormAjax
};