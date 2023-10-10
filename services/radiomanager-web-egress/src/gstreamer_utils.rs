use gstreamer::{Element, ElementFactory};

pub(crate) fn make_element(factory: &str) -> Element {
    ElementFactory::make(factory)
        .build()
        .expect(&format!("Unable to make {} element", factory))
}
