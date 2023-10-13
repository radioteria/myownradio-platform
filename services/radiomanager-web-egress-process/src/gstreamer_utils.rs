use gstreamer::prelude::*;
use gstreamer::{Caps, Element, ElementFactory};

pub(crate) fn make_element(factory: &str) -> Element {
    ElementFactory::make(factory)
        .build()
        .expect(&format!("Unable to make {} element", factory))
}

pub(crate) fn make_capsfilter(caps: &Caps) -> Element {
    let capsfilter = make_element("capsfilter");

    capsfilter.set_property("caps", caps);

    capsfilter
}
