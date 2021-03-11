pub fn div_ceil(a: usize, b: usize) -> usize {
    (a as f32 / b as f32).ceil() as usize
}
