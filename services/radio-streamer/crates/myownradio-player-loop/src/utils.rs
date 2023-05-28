/// Returns the minimum value between `threshold` and `value`.
///
/// If `value` is less than `threshold`, the default value of `T` is returned.
/// Otherwise, `value` is returned.
pub(crate) fn threshold_minimum<T: PartialOrd + Default>(threshold: &T, value: T) -> T {
    if &value < threshold {
        T::default()
    } else {
        value
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_threshold_minimum() {
        // Test case 1: value is less than threshold
        let threshold = 5;
        let value = 3;
        let result = threshold_minimum(&threshold, value);
        assert_eq!(result, 0);

        // Test case 2: value is equal to threshold
        let threshold = 10;
        let value = 10;
        let result = threshold_minimum(&threshold, value);
        assert_eq!(result, 10);

        // Test case 3: value is greater than threshold
        let threshold = 10;
        let value = 15;
        let result = threshold_minimum(&threshold, value);
        assert_eq!(result, 15);
    }
}
