mod stream_service;
mod stream_service_utils;

pub(crate) use self::stream_service::StreamService;
pub(crate) use self::stream_service::StreamServiceError;
pub(crate) use self::stream_service::StreamServiceFactory;
pub(crate) use self::stream_service_utils::get_now_playing;
