use crate::data_structures::{StreamId, UserId};
use crate::http_server::response::Response;
use crate::mysql_client::MySqlClient;
use crate::storage::db::repositories::{stream_destinations, StreamDestination};
use actix_web::web::{Data, Json, Path};
use actix_web::HttpResponse;
use serde_json::json;

pub(crate) async fn get_stream_destinations(
    user_id: UserId,
    mysql_client: Data<MySqlClient>,
) -> Response {
    let mut connection = mysql_client.connection().await?;
    let destinations =
        stream_destinations::get_stream_destinations(&mut connection, &user_id).await?;

    Ok(HttpResponse::Ok().json(
        destinations
            .into_iter()
            .map(|dest| {
                json!({
                    "id": dest.id,
                    "channelId": dest.stream_id,
                    "destination": dest.destination_json
                })
            })
            .collect::<Vec<_>>(),
    ))
}

pub(crate) async fn create_stream_destination(
    user_id: UserId,
    stream_id: StreamId,
    mysql_client: Data<MySqlClient>,
) -> Response {
    let mut connection = mysql_client.connection().await?;

    let destination = StreamDestination::RTMP {
        rtmp_url: String::new(),
        streaming_key: String::new(),
    };

    stream_destinations::create_stream_destination(
        &mut connection,
        &user_id,
        &stream_id,
        &destination,
    )
    .await?;

    Ok(HttpResponse::Created().finish())
}

pub(crate) async fn delete_stream_destination(
    id: Path<i32>,
    user_id: UserId,
    mysql_client: Data<MySqlClient>,
) -> Response {
    let mut connection = mysql_client.connection().await?;

    stream_destinations::delete_stream_destination(&mut connection, &id, &user_id).await?;

    Ok(HttpResponse::Created().finish())
}

pub(crate) async fn update_stream_destination(
    id: Path<i32>,
    user_id: UserId,
    stream_destination: Json<StreamDestination>,
    mysql_client: Data<MySqlClient>,
) -> Response {
    let mut connection = mysql_client.connection().await?;

    stream_destinations::update_stream_destination(
        &mut connection,
        &id,
        &user_id,
        &stream_destination,
    )
    .await?;

    Ok(HttpResponse::Created().finish())
}
