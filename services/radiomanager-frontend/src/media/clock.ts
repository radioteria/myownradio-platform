import makeDebug from 'debug'

const debug = makeDebug('Clock')

export class Clock {
  private previousPts: null | number = null
  private time: number = 0

  constructor(private readonly startTimeMillis: number) {}

  public getTime() {
    return this.time
  }

  public advanceTimeByPts(nextPts: number) {
    if (this.previousPts !== null && this.previousPts > nextPts) {
      debug('Backward-going timestamps detected: prev=%d, next=%d', this.previousPts, nextPts)
    }

    const durationSincePrevPts = Math.abs(nextPts - (this.previousPts ?? nextPts))
    this.time += durationSincePrevPts
    this.previousPts = nextPts
  }

  public resetPts() {
    this.previousPts = null
  }

  public async sync(currentTimeMillis: number) {
    const runningTimeMillis = currentTimeMillis - this.startTimeMillis
    const buffersTimeMillis = this.getTime()

    await new Promise<void>((resolve) => {
      window.setTimeout(() => resolve(), buffersTimeMillis - runningTimeMillis)
    })
  }
}
