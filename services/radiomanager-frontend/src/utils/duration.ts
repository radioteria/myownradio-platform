export class Duration {
  private constructor(
    private readonly millis: number,
    private readonly seconds: number,
  ) {}

  public static fromMillis = (millis: number) => {
    return new Duration(millis, 0)
  }

  public static fromSeconds = (seconds: number) => {
    return new Duration(0, seconds)
  }

  public toMillis = () => {
    return this.millis + this.seconds * 1000
  }

  public toSeconds = () => {
    return this.seconds + this.millis / 1000
  }

  public addSeconds = (seconds: number) => {
    return new Duration(this.millis, this.seconds + seconds)
  }

  public addMillis = (millis: number) => {
    return new Duration(this.millis + millis, this.seconds)
  }

  public add = (duration: Duration) => {
    return new Duration(this.millis + duration.millis, this.seconds + duration.seconds)
  }

  public sub = (duration: Duration) => {
    return new Duration(this.millis - duration.millis, this.seconds - duration.seconds)
  }

  public filterBelow = (threshold: Duration) => {
    return this.toMillis() < threshold.toMillis() ? ZERO : this
  }

  public isNeg = () => {
    return this.toMillis() < 0
  }

  public toString = () => {
    return `${this.toMillis().toFixed(0)}ms`
  }
}

export const ZERO = Duration.fromMillis(0)
