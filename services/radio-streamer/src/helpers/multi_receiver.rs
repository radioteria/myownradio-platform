use crate::{unwrap_some, upgrade_weak};
use actix_rt::task::JoinHandle;
use futures::channel::mpsc;
use futures::StreamExt;
use std::collections::HashSet;
use std::mem;
use std::sync::{Arc, Mutex};
use std::time::Duration;

#[derive(Clone)]
pub(crate) struct MultiReceiver<T: Clone + 'static> {
    inner: Arc<Inner<T>>,
}

impl<T: Clone + 'static> MultiReceiver<T> {
    pub(crate) fn new(
        source_receiver: mpsc::Receiver<T>,
        channel_close_timeout: Duration,
        child_sender_buffer_size: usize,
    ) -> Self {
        let inner = Inner::new(
            source_receiver,
            channel_close_timeout,
            child_sender_buffer_size,
        );

        Self { inner }
    }

    pub(crate) fn create_receiver(&self) -> mpsc::Receiver<T> {
        self.inner.create_receiver()
    }
}

#[derive(Clone)]
struct SenderEntry<T> {
    buffers_sent: usize,
    buffers_dropped: usize,
    sender: mpsc::Sender<T>,
}

impl<T> SenderEntry<T> {
    pub(crate) fn new(sender: mpsc::Sender<T>) -> Self {
        Self {
            sender,
            buffers_sent: 0,
            buffers_dropped: 0,
        }
    }
}

struct Inner<T: Clone + 'static> {
    // Static
    channel_close_timeout: Duration,
    child_sender_buffer_size: usize,
    // Dynamic
    children_senders: Arc<Mutex<Vec<SenderEntry<T>>>>,
    loop_handle_container: Arc<Mutex<Option<JoinHandle<()>>>>,
    channel_close_container: Arc<Mutex<Option<JoinHandle<()>>>>,
}

impl<T: Clone + 'static> Inner<T> {
    pub(crate) fn new(
        source_receiver: mpsc::Receiver<T>,
        channel_close_timeout: Duration,
        child_sender_buffer_size: usize,
    ) -> Arc<Self> {
        let children_senders = Arc::default();
        let loop_handle_container = Arc::default();
        let channel_close_container = Arc::default();

        let inner = Arc::new(Self {
            // Static
            channel_close_timeout,
            child_sender_buffer_size,
            // Dynamic
            children_senders: Arc::clone(&children_senders),
            loop_handle_container: Arc::clone(&loop_handle_container),
            channel_close_container,
        });

        let loop_handle = actix_rt::spawn({
            let children_senders = children_senders.clone();
            let mut source_receiver = source_receiver;

            let inner_weak = Arc::downgrade(&inner);

            async move {
                while let Some(t) = source_receiver.next().await {
                    let mut locked_senders = children_senders
                        .lock()
                        .expect("Unable to lock children_senders on sending data");

                    let mut senders_to_remove = HashSet::new();

                    for (index, sender_entry) in locked_senders.iter_mut().enumerate() {
                        let result = sender_entry.sender.try_send(t.clone());
                        match result {
                            Ok(_) => {
                                sender_entry.buffers_sent += 1;
                            }
                            Err(error) => {
                                if error.is_full() {
                                    sender_entry.buffers_dropped += 1;
                                } else if error.is_disconnected() {
                                    senders_to_remove.insert(index);
                                }
                            }
                        }
                    }

                    if !senders_to_remove.is_empty() {
                        *locked_senders = mem::take(&mut *locked_senders)
                            .into_iter()
                            .enumerate()
                            .filter(|(i, _)| !senders_to_remove.contains(&i))
                            .map(|(_, sender)| sender)
                            .collect::<Vec<_>>();

                        if locked_senders.is_empty() {
                            let inner = upgrade_weak!(inner_weak);

                            inner.start_channel_close_timeout();
                        }
                    }
                }
            }
        });

        let _ = loop_handle_container
            .lock()
            .expect("Unable to obtain lock for loop_handle_container")
            .insert(loop_handle);

        inner.start_channel_close_timeout();

        inner
    }

    pub(crate) fn create_receiver(&self) -> mpsc::Receiver<T> {
        let (tx, rx) = mpsc::channel::<T>(self.child_sender_buffer_size);

        self.cancel_channel_close_timeout();

        self.children_senders
            .lock()
            .expect("Unable to obtain lock for children_senders on adding sender")
            .push(SenderEntry::new(tx));

        rx
    }

    fn start_channel_close_timeout(&self) {
        let loop_handle_container_weak = Arc::downgrade(&self.loop_handle_container);
        let channel_close_timeout = self.channel_close_timeout.clone();

        self.channel_close_container
            .lock()
            .unwrap()
            .get_or_insert_with(|| {
                actix_rt::spawn(async move {
                    actix_rt::time::sleep(channel_close_timeout).await;

                    let loop_handle_container = upgrade_weak!(loop_handle_container_weak);
                    let maybe_loop_handle = loop_handle_container
                        .lock()
                        .expect("Unable to lock loop_handle_container")
                        .take();
                    let loop_handle = unwrap_some!(maybe_loop_handle);

                    loop_handle.abort();
                })
            });
    }

    fn cancel_channel_close_timeout(&self) {
        if let Some(handle) = self.channel_close_container.lock().unwrap().take() {
            handle.abort();
        }
    }
}
