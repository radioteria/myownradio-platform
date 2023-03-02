import React from 'react'
import { RadioPlayerStore } from './RadioPlayerStore'
import { Observer } from 'mobx-react-lite'
import styles from './RadioPlayerStats.module.scss'

interface Props {
  radioPlayerStore: RadioPlayerStore
}

export const RadioPlayerStats: React.FC<Props> = ({ radioPlayerStore }) => {
  return (
    <Observer>
      {() => {
        const bufferedAheadTime = radioPlayerStore.bufferedTime - radioPlayerStore.currentTime

        const activeMetadataIndex = radioPlayerStore.metadataEntries.findIndex(
          (entry) => radioPlayerStore.currentTime >= entry.pts,
        )

        return (
          <div className={styles.root}>
            <section className={styles.section}>
              <span className={styles.label}>time:</span>
              <span className={styles.value}>{radioPlayerStore.currentTime}s</span>
            </section>
            <section>
              <span className={styles.label}>buffer:</span>
              <span className={styles.value}>+{bufferedAheadTime.toFixed(1)}s</span>
            </section>
            <section>
              <span className={styles.label}>metadata:</span>
              <span className={styles.value}>
                {radioPlayerStore.metadataEntries.map((entry, i) => (
                  <div
                    style={{
                      fontWeight: activeMetadataIndex === i ? 'bold' : 'normal',
                    }}
                    key={`${i}`}
                  >
                    {entry.metadata.stream_title}
                  </div>
                ))}
              </span>
            </section>
          </div>
        )
      }}
    </Observer>
  )
}
