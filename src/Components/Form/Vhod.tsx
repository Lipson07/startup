// Vhod.tsx
import React, { useState } from 'react';
import style from "../../style/Form/Vhod.module.scss";
import { useDispatch } from 'react-redux';
import { setUserData } from '../../store/user';

interface FormData {
  email: string;
  password: string;
}

interface LoginProps {
  onSuccess: () => void;
  onError: (errorMessage: string) => void;
  onNavigateToRegistration: () => void;
}

function Vhod({ onSuccess, onError, onNavigateToRegistration }: LoginProps) {
  const [formData, setFormData] = useState<FormData>({
    email: '',
    password: '',
  });
  const dispatch = useDispatch();
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<{[key: string]: string}>({});

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    if (errors[name]) {
      setErrors(prev => {
        const newErrors = {...prev};
        delete newErrors[name];
        return newErrors;
      });
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      // Логика входа
      const loginResponse = await fetch('http://127.0.0.1:8000/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          email: formData.email,
          password: formData.password
        }),
      });

      if (!loginResponse.ok) {
        throw new Error('Ошибка при входе');
      }

      const loginData = await loginResponse.json();

      if (loginData.success) {
        dispatch(setUserData({
          name: loginData.user.name,
          email: loginData.user.email,
          password: formData.password
        }));
        onSuccess();
      } else {
        throw new Error(loginData.error || 'Неверный email или пароль');
      }

    } catch (error: any) {
      console.error('Ошибка:', error);
      onError(error.message || 'Произошла ошибка при входе');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className={style.container}>
      <h1>Вход</h1>

      <form onSubmit={handleSubmit}>
        <div className={style.inputGroup}>
          <label htmlFor="email">Почта</label>
          <input
            type="email"
            id="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            placeholder="your@email.com"
            disabled={loading}
            required
            className={errors.email ? style.inputError : ''}
          />
          {errors.email && <span className={style.errorText}>{errors.email}</span>}
        </div>

        <div className={style.inputGroup}>
          <label htmlFor="password">Пароль</label>
          <input
            type="password"
            id="password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            placeholder="Введите ваш пароль"
            disabled={loading}
            required
            className={errors.password ? style.inputError : ''}
          />
          {errors.password && <span className={style.errorText}>{errors.password}</span>}
        </div>

        <button
          type="submit"
          disabled={loading}
          className={style.submitButton}
        >
          {loading ? 'Вход...' : 'Войти'}
        </button>

        <div className={style.registerLink}>
          Нет аккаунта? <button type="button" onClick={onNavigateToRegistration} className={style.linkButton}>Зарегистрироваться</button>
        </div>
      </form>
    </div>
  );
}

export default Vhod;