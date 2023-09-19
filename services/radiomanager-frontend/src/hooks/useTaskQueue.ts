import { useCallback, useEffect, useRef, useState } from 'react'
import makeDebug from 'debug'

const debug = makeDebug('useQueue')

export const useTaskQueue = <Task>(onTask: (task: Task, signal: AbortSignal) => Promise<void>) => {
  const [tasks, setTasks] = useState<readonly Task[]>([])

  const abortControllersRef = useRef<AbortController[]>([])

  useEffect(() => {
    if (tasks.length === 0) {
      debug('Task queue is empty')
      return
    }

    const [task, ...restTasks] = tasks

    const abortController = new AbortController()

    abortControllersRef.current.push(abortController)

    setTasks(restTasks)

    debug('Processing task: %s', task)

    onTask(task, abortController.signal)
      .then(() => {
        debug('Task processing finished: %s', task)
      })
      .catch((error) => {
        debug('Task processing failed: %s', task, error)
      })
      .finally(() => {
        const abortControllerIndex = abortControllersRef.current.indexOf(abortController)

        if (abortControllerIndex !== -1) {
          abortControllersRef.current.splice(abortControllerIndex, 1)
        }
      })
  }, [onTask, tasks])

  useEffect(() => {
    const controllers = abortControllersRef.current

    return () => {
      controllers.forEach((ctrl) => ctrl.abort())
    }
  }, [])

  const addTask = useCallback((task: Task) => {
    debug('Adding task to the queue: %s', task)
    setTasks((prevItems) => [...prevItems, task])
  }, [])

  return { addTask }
}
