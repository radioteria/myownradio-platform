import { atom, getDefaultStore } from 'jotai'
import PQueue from 'p-queue'
import {
  MediaUploadQueueItem,
  MediaUploadResult,
  MediaUploadResultStatus,
  UploadedMediaTrack,
  UploadedTrackType,
} from './MediaUploaderTypes'
import { uploadTrackToChannel, uploadTrackToLibrary } from '@/api'
import { shiftFromArrayAtom, pushToArrayAtom } from './MediaUploaderStore.utils'

const isAbortedUploadError = (error: unknown) =>
  error instanceof DOMException && error.name === 'AbortError'

export class MediaUploaderStore {
  private readonly store = getDefaultStore()
  private abortController: AbortController | null = null

  public readonly uploadQueueAtom = atom<readonly MediaUploadQueueItem[]>([])

  private pushItemToQueue = (item: MediaUploadQueueItem) => {
    pushToArrayAtom(this.uploadQueueAtom, item, this.store)
  }

  private pullItemFromQueue = (): MediaUploadQueueItem | null => {
    return shiftFromArrayAtom(this.uploadQueueAtom, this.store)
  }

  private resetItemsQueue = () => {
    this.store.set(this.uploadQueueAtom, [])
  }

  public readonly uploadResultsAtom = atom<readonly MediaUploadResult[]>([])

  private registerFailedUploadResult = (error: unknown, queueItem: MediaUploadQueueItem) => {
    const status = MediaUploadResultStatus.FAILED
    pushToArrayAtom(this.uploadResultsAtom, { status, error, queueItem }, this.store)
  }

  private registerSuccessfulUploadResult = (
    uploadedTrack: UploadedMediaTrack,
    queueItem: MediaUploadQueueItem,
  ) => {
    const status = MediaUploadResultStatus.UPLOADED
    pushToArrayAtom(this.uploadResultsAtom, { status, uploadedTrack, queueItem }, this.store)
  }

  private resetUploadResults = () => this.store.set(this.uploadResultsAtom, [])

  public readonly lastUploadedTrackAtom = atom<UploadedMediaTrack | null>(null)

  private setLastUploadedTrack = (track: UploadedMediaTrack | null) => {
    this.store.set(this.lastUploadedTrackAtom, track)
  }

  public readonly currentQueueItemAtom = atom<MediaUploadQueueItem | null>(null)

  private setCurrentQueueItem = (item: MediaUploadQueueItem | null) => {
    this.store.set(this.currentQueueItemAtom, item)
  }

  private isUploading = (): boolean => {
    return !!this.store.get(this.currentQueueItemAtom)
  }

  public upload = (file: File, channelId?: number) => {
    const queueItem = { file, channelId }

    if (!this.isUploading()) {
      this.resetUploadResults()
    }

    this.pushItemToQueue(queueItem)
  }

  public abort = () => {
    this.abortController?.abort()
  }

  public init = () => {
    const uploadPromiseQueue = new PQueue({ concurrency: 1 })

    const uploadQueueListener = async () => {
      const queueItem = this.pullItemFromQueue()

      if (!queueItem) {
        this.setCurrentQueueItem(null)
        return
      }

      this.setCurrentQueueItem(queueItem)

      this.abortController = new AbortController()

      try {
        const { channelId, file } = queueItem

        const uploadedTrack = channelId
          ? await uploadTrackToChannel(channelId, file, this.abortController.signal).then(
              (track) => ({
                channelId,
                type: UploadedTrackType.CHANNEL as const,
                track: {
                  ...track,
                  uniqueId: track.uniqueId,
                  artist: track.artist ?? '',
                  album: track.album ?? '',
                  genre: track.genre ?? '',
                  trackNumber: String(track.trackNumber),
                },
              }),
            )
          : await uploadTrackToLibrary(file, this.abortController.signal).then((track) => ({
              type: UploadedTrackType.LIBRARY as const,
              track,
            }))

        this.registerSuccessfulUploadResult(uploadedTrack, queueItem)
        this.setLastUploadedTrack(uploadedTrack)
      } catch (error) {
        if (isAbortedUploadError(error)) {
          this.resetItemsQueue()
          return
        }

        this.registerFailedUploadResult(error, queueItem)
      } finally {
        this.abortController = null
      }
    }

    return this.store.sub(this.uploadQueueAtom, () => uploadPromiseQueue.add(uploadQueueListener))
  }
}
