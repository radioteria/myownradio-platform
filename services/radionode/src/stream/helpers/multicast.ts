import { Writable } from 'stream';

export class Multicast extends Writable {
  private clients = [];
  private draining = [];

  public _write(chunk: Buffer | string, enc: string, callback: () => void): boolean {
    this.clients.forEach((c, i) => {
      if (this.draining[i]) {
        return;
      }

      const ok = c.write(chunk, enc);

      if (!ok) {
        this.draining[i] = true;
      }
    });
    callback();
    return true;
  }

  public add(client: Writable) {
    client.on('close', () => this.removeClient(client));
    client.on('error', () => this.removeClient(client));
    client.on('drain', () => {
      const clientIndex = this.clients.indexOf(client);
      if (clientIndex > -1) {
        this.draining[clientIndex] = false;
      }
    });

    this.clients.push(client);
  }

  public clear() {
    this.clients.forEach(c => c.end());
    this.clients = [];
  }

  public removeClient(client: Writable) {
    const clientIndex = this.clients.indexOf(client);
    if (clientIndex > -1) {
      this.clients.splice(clientIndex, 1);
    }
  }

  public count(): number {
    return this.clients.length;
  }
}

export const multicast = () => new Multicast();
