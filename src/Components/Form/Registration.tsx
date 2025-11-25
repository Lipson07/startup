
import React, { useState } from 'react'
import style from "../../style/Form/Registration.module.scss"

interface FormData {
  name: string;
  email: string;
  password: string;
}

interface RegistrationProps {
  onSuccess: () => void;
  onError: (errorMessage: string) => void;
}

function Registration({ onSuccess, onError }: RegistrationProps) {
  const [formData, setFormData] = useState<FormData>({
    name: '',
    email: '',
    password: '',
  })

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

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    setErrors({})

    try {
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

      // Если проверка пройдена, переходим к верификации email
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

          <button
              type="submit"
              disabled={loading}
              className={style.submitButton}
          >
            {loading ? 'Проверка...' : 'Продолжить'}
          </button>
        </form>
      </div>
  )
}

export default Registration