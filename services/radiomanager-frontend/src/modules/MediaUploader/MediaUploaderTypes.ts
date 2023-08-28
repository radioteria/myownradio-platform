import { UserChannelTrack, UserTrack } from '@/api/api.types'

export enum UploadedTrackType {
  LIBRARY = 'LIBRARY',
  CHANNEL = 'CHANNEL',
}

interface UploadedLibraryTrack {
  readonly type: UploadedTrackType.LIBRARY
  readonly track: UserTrack
}

interface UploadedChannelTrack {
  readonly type: UploadedTrackType.CHANNEL
  readonly channelId: number
  readonly track: UserChannelTrack
}

export type UploadedMediaTrack = UploadedLibraryTrack | UploadedChannelTrack

export interface MediaUploadQueueItem {
  readonly channelId?: number
  readonly file: File
}

export enum MediaUploadResultStatus {
  UPLOADED,
  FAILED,
}

interface MediaUploadSuccessfulResult {
  readonly status: MediaUploadResultStatus.UPLOADED
  readonly queueItem: MediaUploadQueueItem
  readonly uploadedTrack: UploadedMediaTrack
}

interface MediaUploadFailedResult {
  readonly status: MediaUploadResultStatus.FAILED
  readonly queueItem: MediaUploadQueueItem
  readonly error: unknown
}

export type MediaUploadResult = MediaUploadSuccessfulResult | MediaUploadFailedResult
