import EventEmitter from 'events'
import { UserEvent, UserEventSchema } from './UserEvents'
import makeDebug from 'debug'
import { config } from '@/config'

const debug = makeDebug('UserEventSource')

const RECONNECT_INTERVAL = 5_000
const INTERNAL_EVENT_NAME = 'decodedMessage'
const BACKEND_BASE_URL = config.NEXT_PUBLIC_RADIOMANAGER_BACKEND_URL

type Unsubscribe = () => void

export class UserEventSource {
  private readonly eventEmitter = new EventEmitter()

  private eventSource: EventSource | null = null
  private reconnectTimeoutId: number | null = null

  private shouldReconnect = true

  public connect() {
    // Disconnect if already connected (will cause reconnect)
    this.eventSource?.close()

    const url = new URL(`${BACKEND_BASE_URL}/pubsub/channel/user/subscribe`)
    const token = new URL(window.location.href).searchParams.get('token')
    if (token) {
      url.searchParams.set('token', token)
    }
    const eventSource = new EventSource(url, { withCredentials: true })

    this.eventSource = eventSource

    eventSource.addEventListener('message', (msgEvent) => {
      try {
        const json = JSON.parse(msgEvent.data)
        const userEvent = UserEventSchema.parse(json)
        this.eventEmitter.emit(INTERNAL_EVENT_NAME, userEvent)
      } catch (e) {
        debug('Unable to decode user event: error = %s', e)
      }
    })

    eventSource.addEventListener('error', (error) => {
      debug('Error on reading user events: error = %s, reconnect = %b', error, this.shouldReconnect)

      if (this.shouldReconnect) {
        this.reconnectTimeoutId = window.setTimeout(() => this.connect(), RECONNECT_INTERVAL)
      }
    })

    eventSource.addEventListener('open', () => {
      debug('Connected to user events')
    })
  }

  public subscribe(listener: (event: UserEvent) => void): Unsubscribe {
    this.eventEmitter.addListener(INTERNAL_EVENT_NAME, listener)

    return () => {
      this.eventEmitter.removeListener(INTERNAL_EVENT_NAME, listener)
    }
  }

  public disconnect() {
    this.shouldReconnect = false

    if (this.reconnectTimeoutId !== null) {
      window.clearTimeout(this.reconnectTimeoutId)
      this.reconnectTimeoutId = null
    }

    this.eventSource?.close()
    this.eventSource = null
  }
}
