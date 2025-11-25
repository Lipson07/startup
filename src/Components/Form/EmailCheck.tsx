// EmailCheck.tsx
import React, { useState, useRef, useEffect } from 'react';
import style from "../../style/Form/EmailCheck.module.scss";

interface EmailCheckProps {
    email: string;
    onBack?: () => void;
}

function EmailCheck({ email, onBack }: EmailCheckProps) {
    const [code, setCode] = useState<string[]>(['', '', '', '', '', '']);
    const [loading, setLoading] = useState(false);
    const inputRefs = useRef<(HTMLInputElement | null)[]>([]);

    useEffect(() => {
        if (inputRefs.current[0]) {
            inputRefs.current[0]?.focus();
        }
    }, []);

    const handleInputChange = (index: number, value: string) => {
        if (value.length <= 1 && /^\d*$/.test(value)) {
            const newCode = [...code];
            newCode[index] = value;
            setCode(newCode);


            if (value && index < 5) {
                inputRefs.current[index + 1]?.focus();
            }
        }
    };

    const handleKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Backspace' && !code[index] && index > 0) {
            inputRefs.current[index - 1]?.focus();
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const verificationCode = code.join('');

        if (verificationCode.length !== 6) {
            alert('Пожалуйста, введите все 6 цифр кода');
            return;
        }

        setLoading(true);
//API
        try {

            console.log('Отправка POST запроса с кодом:', verificationCode);


            setTimeout(() => {
                setLoading(false);

            }, 1000);
        } catch (error) {
            console.error('Ошибка при проверке кода:', error);
            setLoading(false);
        }
    };

    const handleResendCode = () => {
        console.log('Запрос на повторную отправку кода');
        // Логика повторной отправки кода
    };

    return (
        <div className={style.container}>
            <h1>Подтверждение почты</h1>
            <p className={style.subtitle}>
                Мы отправили 6-значный код подтверждения на<br />
                <strong>{email}</strong>
            </p>

            <form onSubmit={handleSubmit}>
                <div className={style.codeInputs}>
                    {code.map((digit, index) => (
                        <input
                            key={index}
                            ref={el => inputRefs.current[index] = el}
                            type="text"
                            inputMode="numeric"
                            pattern="[0-9]*"
                            maxLength={1}
                            value={digit}
                            onChange={(e) => handleInputChange(index, e.target.value)}
                            onKeyDown={(e) => handleKeyDown(index, e)}
                            disabled={loading}
                            className={style.codeInput}
                            required
                        />
                    ))}
                </div>

                <button
                    type="submit"
                    disabled={loading || code.join('').length !== 6}
                    className={style.verifyButton}
                >
                    {loading ? 'Проверка...' : 'Подтвердить'}
                </button>
            </form>

            <div className={style.resendSection}>
                <p>Не получили код?</p>
                <button
                    type="button"
                    className={style.resendButton}
                    onClick={handleResendCode}
                    disabled={loading}
                >
                    Отправить код повторно
                </button>
            </div>

            {onBack && (
                <button
                    type="button"
                    className={style.resendButton}
                    onClick={onBack}
                    disabled={loading}
                >
                    Назад к регистрации
                </button>
            )}
        </div>
    );
}

export default EmailCheck;