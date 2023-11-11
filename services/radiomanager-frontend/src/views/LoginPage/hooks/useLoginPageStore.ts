import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

export const useLoginPageStore = () => {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')

  const [isValidEmail, setIsValidEmail] = useState(true)
  const [isValidPassword, setIsValidPassword] = useState(true)

  const handleEmailChange = useCallback((email: string) => {
    setEmail(email)
    setIsValidEmail(true)
  }, [])

  const handlePasswordChange = useCallback((password: string) => {
    setPassword(password)
    setIsValidPassword(true)
  }, [])

  const abortController = useMemo(() => new AbortController(), [])

  useEffect(() => {
    return () => {
      abortController.abort()
    }
  }, [abortController])

  const handleSubmitLoginForm = () => {
    if (!isValidEmail || !isValidPassword) return

    if (email.length === 0) {
      setIsValidEmail(false)
      return
    }

    if (password.length === 0) {
      setIsValidPassword(false)
      return
    }

    // TODO Submit form
  }

  return {
    email,
    password,
    isValidEmail,
    isValidPassword,
    handleEmailChange,
    handlePasswordChange,
  }
}
