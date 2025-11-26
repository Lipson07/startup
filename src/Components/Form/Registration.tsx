// Registration.tsx
import React, { useState } from 'react'
import style from "../../style/Form/Registration.module.scss"
import { useDispatch } from 'react-redux';
import { setUserData } from '../../store/user';

interface FormData {
  name: string;
  email: string;
  password: string;
  confirmPassword: string;
}

interface RegistrationProps {
  onSuccess: () => void;
  onError: (errorMessage: string) => void;
  onNavigateToLogin: () => void;
}

function Registration({ onSuccess, onError, onNavigateToLogin }: RegistrationProps) {
  const [formData, setFormData] = useState<FormData>({
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
  })
  const dispatch = useDispatch();
  const [loading, setLoading] = useState(false)
  const [errors, setErrors] = useState<{[key: string]: string}>({})

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target
    setFormData(prev => ({
      ...prev,
      [name]: value
    }))
    if (errors[name]) {
      setErrors(prev => {
        const newErrors = {...prev}
        delete newErrors[name]
        return newErrors
      })
    }
  }

  const validateForm = (): boolean => {
    const newErrors: {[key: string]: string} = {}

    if (formData.name.length < 2) {
      newErrors.name = 'Имя должно содержать минимум 2 символа'
    }

    if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Введите корректный email'
    }

    if (formData.password.length < 6) {
      newErrors.password = 'Пароль должен содержать минимум 6 символов'
    }

    if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Пароли не совпадают'
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (!validateForm()) {
      return
    }

    setLoading(true)
    setErrors({})

    try {
      dispatch(setUserData({
        name: formData.name,
        email: formData.email,
        password: formData.password
      }));

      const checkResponse = await fetch(`http://127.0.0.1:8000/api/check-user?email=${encodeURIComponent(formData.email)}&name=${encodeURIComponent(formData.name)}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      })

      if (!checkResponse.ok) {
        throw new Error('Ошибка при проверке данных')
      }

      const checkData = await checkResponse.json()

      if (checkData.email_exists) {
        throw new Error('Пользователь с таким email уже существует')
      }

      if (checkData.name_exists) {
        throw new Error('Пользователь с таким именем уже существует')
      }

      onSuccess()

    } catch (error: any) {
      console.error('Ошибка:', error)
      onError(error.message || 'Произошла ошибка при регистрации')
    } finally {
      setLoading(false)
    }
  }

  return (
      <div className={style.container}>
        <h1>Регистрация</h1>

        <form onSubmit={handleSubmit}>
          <div className={style.inputGroup}>
            <label htmlFor="name">Имя</label>
            <input
                type="text"
                id="name"
                name="name"
                value={formData.name}
                onChange={handleChange}
                placeholder="Введите ваше имя"
                disabled={loading}
                required
                className={errors.name ? style.inputError : ''}
            />
            {errors.name && <span className={style.errorText}>{errors.name}</span>}
          </div>

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
                placeholder="Создайте пароль (минимум 6 символов)"
                disabled={loading}
                required
                minLength={6}
                className={errors.password ? style.inputError : ''}
            />
            {errors.password && <span className={style.errorText}>{errors.password}</span>}
          </div>

          <div className={style.inputGroup}>
            <label htmlFor="confirmPassword">Подтвердите пароль</label>
            <input
                type="password"
                id="confirmPassword"
                name="confirmPassword"
                value={formData.confirmPassword}
                onChange={handleChange}
                placeholder="Повторите пароль"
                disabled={loading}
                required
                className={errors.confirmPassword ? style.inputError : ''}
            />
            {errors.confirmPassword && <span className={style.errorText}>{errors.confirmPassword}</span>}
          </div>

          <button
              type="submit"
              disabled={loading}
              className={style.submitButton}
          >
            {loading ? 'Проверка...' : 'Зарегистрироваться'}
          </button>

          <div className={style.loginLink}>
            Уже есть аккаунт? <button type="button" onClick={onNavigateToLogin} className={style.linkButton}>Войти</button>
          </div>
        </form>
      </div>
  )
}

export default Registration