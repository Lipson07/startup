// Regmodule.tsx
import React, { useState } from 'react';
import { Routes, Route, useNavigate } from 'react-router-dom';
import Registration from "./Registration.tsx";
import EmailCheck from "./EmailCheck.tsx";
import Vhod from "./Vhod.tsx";
import style from "../../style/Form/Regmodule.module.scss";

function Regmodule() {
    const [notification, setNotification] = useState<{message: string; type: 'success' | 'error'} | null>(null);
    const navigate = useNavigate();

    const showNotification = (message: string, type: 'success' | 'error') => {
        setNotification({ message, type });
        setTimeout(() => {
            setNotification(null);
        }, 5000);
    };

    
    const handleRegistrationSuccess = () => {
        showNotification('Проверка пройдена! Переходим к подтверждению email...', 'success');
        setTimeout(() => {
            navigate('/auth/email-check');
        }, 1500);
    };

    const handleRegistrationError = (errorMessage: string) => {
        showNotification(errorMessage, 'error');
    };


    const handleEmailCheckNotification = (message: string, type: 'success' | 'error') => {
        showNotification(message, type);
    };

    const handleEmailCheckSuccess = () => {
        showNotification('Регистрация завершена успешно!', 'success');
 
        setTimeout(() => {
            navigate('/auth');
        }, 2000);
    };



    const handleLoginSuccess = () => {
        showNotification('Вход выполнен успешно!', 'success');
 
        setTimeout(() => {
            navigate('/main');
        }, 1500);
    };

    const handleLoginError = (errorMessage: string) => {
        showNotification(errorMessage, 'error');
    };

    return (
        <div className={style.main}>
            <Routes>
                <Route 
                    path="/" 
                    element={
                        <Vhod
                            onSuccess={handleLoginSuccess}
                            onError={handleLoginError}
                            onNavigateToRegistration={() => navigate('/auth/registration')}
                        />
                    } 
                />
                
          
                <Route 
                    path="/registration" 
                    element={
                        <Registration
                            onSuccess={handleRegistrationSuccess}
                            onError={handleRegistrationError}
                            onNavigateToLogin={() => navigate('/auth')}
                        />
                    } 
                />
                
          
                <Route 
                    path="/email-check" 
                    element={
                        <EmailCheck 
                         
                            onSuccess={handleEmailCheckSuccess}
                            onNotification={handleEmailCheckNotification}
                        />
                    } 
                />
            </Routes>

            {notification && (
                <div className={`${style.notification} ${style[notification.type]}`}>
                    {notification.message}
                </div>
            )}
        </div>
    );
}

export default Regmodule;