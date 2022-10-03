use actix_rt::task::JoinHandle;
use futures::channel::mpsc;
use futures::StreamExt;
use std::collections::HashSet;
use std::future::Future;
use std::mem;
use std::sync::{Arc, Mutex};
use std::time::Duration;

#[derive(Clone)]
pub(crate) struct MultiSender {}

struct Inner<T: Clone + 'static> {
    // Static
    no_senders_timeout: Duration,
    child_sender_receive_timeout: Duration,
    child_sender_buffer_size: usize,
    // Dynamic
    children_senders: Arc<Mutex<Vec<mpsc::Sender<T>>>>,
}

impl<T: Clone + 'static> Inner<T> {
    pub(crate) fn new(source_receiver: mpsc::Receiver<T>) -> Self {
        let no_senders_timeout = Duration::from_secs(30);
        let child_sender_receive_timeout = Duration::from_secs(60);
        let child_sender_buffer_size = 10;

        let children_senders: Arc<Mutex<Vec<mpsc::Sender<T>>>> = Arc::default();

        let mut no_senders_timeout_handle_container = None::<JoinHandle<()>>;
        let loop_handle_container = Arc::new(Mutex::new(None::<JoinHandle<()>>));

        let loop_handle = actix_rt::spawn({
            let loop_handle_container = loop_handle_container.clone();

            let children_senders = children_senders.clone();
            let mut source_receiver = source_receiver;

            async move {
                while let Some(t) = source_receiver.next().await {
                    let mut locked_senders = children_senders
                        .lock()
                        .expect("Unable to lock children_senders on sending data");

                    if locked_senders.is_empty() {
                        let loop_handle_container = loop_handle_container.clone();

                        no_senders_timeout_handle_container.get_or_insert_with(move || {
                            let loop_handle_container = loop_handle_container.clone();

                            actix_rt::spawn(async move {
                                actix_rt::time::sleep(no_senders_timeout).await;

                                if let Some(handle) = loop_handle_container
                                    .lock()
                                    .expect("Unable to lock loop_handle_container")
                                    .take()
                                {
                                    handle.abort();
                                }
                            })
                        });
                    } else {
                        if let Some(handle) = no_senders_timeout_handle_container.take() {
                            handle.abort();
                        }
                    }

                    let mut senders_to_remove = HashSet::new();

                    for (index, sender) in locked_senders.iter_mut().enumerate() {
                        let result = sender.try_send(t.clone());
                        if let Err(error) = result {
                            if error.is_full() {
                                // @todo Skip buffers for now. After `child_sender_receive_timeout` remove sender.
                                senders_to_remove.insert(index);
                            } else if error.is_disconnected() {
                                senders_to_remove.insert(index);
                            }
                        }
                    }

                    if !senders_to_remove.is_empty() {
                        *locked_senders = mem::take(&mut *locked_senders)
                            .into_iter()
                            .enumerate()
                            .filter(|(i, _)| !senders_to_remove.contains(&i))
                            .map(|(i, sender)| sender)
                            .collect::<Vec<_>>()
                    }
                }
            }
        });
        let _ = loop_handle_container
            .lock()
            .expect("Unable to lock loop_handle_container")
            .insert(loop_handle);

        Self {
            // Static
            no_senders_timeout,
            child_sender_receive_timeout,
            child_sender_buffer_size,
            // Dynamic
            children_senders,
        }
    }

    pub(crate) fn create_receiver(&self) -> mpsc::Receiver<T> {
        let (tx, rx) = mpsc::channel::<T>(self.child_sender_buffer_size);

        self.children_senders
            .lock()
            .expect("Unable to lock children_senders on adding sender")
            .push(tx);

        rx
    }
}
