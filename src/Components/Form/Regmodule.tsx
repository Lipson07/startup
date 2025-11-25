// Regmodule.tsx
import React, { useState } from 'react';
import Registration from "./Registration.tsx";
import EmailCheck from "./EmailCheck.tsx";
import style from "../../style/Form/Regmodule.module.scss";

function Regmodule() {
    const [currentStep, setCurrentStep] = useState<'registration' | 'emailCheck'>('registration');
    const [notification, setNotification] = useState<{message: string; type: 'success' | 'error'} | null>(null);

    const showNotification = (message: string, type: 'success' | 'error') => {
        setNotification({ message, type });
        setTimeout(() => {
            setNotification(null);
        }, 5000);
    };

    const handleRegistrationSuccess = () => {
        showNotification('Проверка пройдена! Переходим к подтверждению email...', 'success');
        setTimeout(() => {
            setCurrentStep('emailCheck');
        }, 1500);
    };

    const handleRegistrationError = (errorMessage: string) => {
        showNotification(errorMessage, 'error');
    };

    return (
        <div className={style.main}>
            {currentStep === 'registration' && (
                <Registration
                    onSuccess={handleRegistrationSuccess}
                    onError={handleRegistrationError}
                />
            )}
            {currentStep === 'emailCheck' && (
                <EmailCheck />
            )}

            {notification && (
                <div className={`${style.notification} ${style[notification.type]}`}>
                    {notification.message}
                </div>
            )}
        </div>
    );
}

export default Regmodule;