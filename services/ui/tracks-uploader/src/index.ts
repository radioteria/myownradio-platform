import { UIModule } from '@myownradio/io-contracts'

export class TracksUploader implements UIModule {
  public async mount(_: HTMLElement): Promise<void> {
    // @todo
  }

  public async unmount(_: HTMLElement): Promise<void> {
    // @todo
  }

  public test(): string {
    return 'World'
  }
}

const t = new TracksUploader()

console.log(`Hello, ${t.test()}!`)
