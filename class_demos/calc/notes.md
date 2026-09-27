# Stepping through the calculator JavaScript

Walkthrough of `index.html`, `css/main.css`, and `js/main.js`.
Line numbers refer to the current JavaScript, before any live-demo edits.
The summation snippets below are a proposed next demonstration; they have not been added to the application files.

## Start with the HTML and CSS

Look at the calculator in `index.html`.
Notice:
- the `id` attributes that let JavaScript and CSS select individual elements
- the shared `op_button` class that allow for selecting buttons. 
- Briefly open the CSS to show where their appearance comes from.

Show `<script src="js/main.js" defer></script>`. This external script runs
after the HTML has been parsed, so its element lookups can find the page's DOM
objects.

## JS Lines 1–7: Get references to DOM elements

>"These variables hold references to objects representing elements on the
page. We can use those references to register listeners or update the page.”

`querySelector("h1")` selects the first matching heading; `getElementById(...)`
selects an element by its ID. `h1`, `msg`, and `output` refer to elements, not
copies of their displayed text. `const` prevents reassignment of the variable;
it does not prevent changing properties of the referenced element.

## Lines 9–13: How HTML invokes `hello`

Show the HTML:

```html
<h1 onclick="hello()">Calculator</h1>
```

>“The heading has an **inline event handler** in its `onclick` attribute.
When the heading receives a click event, the browser runs that handler, which
**calls**, or **invokes**, the `hello` function.”

The function declaration defines `hello`; it does not run its body immediately.
The parentheses in `hello()` make this a function call inside the handler.

Step through the hello() event handler (HTML), fn (JS), and show in browser.

## Lines 18–24: Register callbacks two ways

>“`addEventListener` registers a callback for a particular event. Registration
happens when the script runs; the browser invokes the callback when the matching
event occurs.” See [MDN: addEventListener](https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener).

**First way: pass an existing named function.**

```javascript
add_btn.addEventListener("click", add);
```

`"click"` is the event type, and `add` is the callback function. We pass the
function itself, without calling it. Writing `add()` here would invoke it during
registration and pass its return value instead. The declarations for `add`,
`subtract`, and `divide` can appear farther down the file because function
declarations are hoisted.

**Second way: supply an anonymous arrow function inline.**

```javascript
mult_btn.addEventListener("click", () => {
    let nums = getOperators();
    output.innerHTML = nums != undefined ? nums[0] * nums[1] : '?';
});
```

The arrow function is the callback. Its body runs on a click, then calls
`getOperators()` and displays the product or `?`. Both styles register functions;
one refers to a named function, and the other creates a function at the call site.

**Question:** Does registering these listeners calculate anything yet?\
**Answer:** No. The callbacks wait for clicks. The calls inside their
bodies run only when the callbacks execute.

## Lines 28–29: Read and convert the input values

Step into `getOperators()` from a callback. Evaluate the expression from the
inside out: find the input element, read its `.value`, pass that string to
`parseFloat`, and store the returned number.

**Question:** Why convert when the HTML already says `type="number"`?\
**Answer:** The HTML type controls the input's numeric UI and validation.
The DOM `.value` property still returns a string. It does not become a JavaScript
number because of the HTML attribute. See [MDN: input value](https://developer.mozilla.org/en-US/docs/Web/API/HTMLInputElement/value).

Enter `2` and `3`, then compare in the Console:

```javascript
document.getElementById("val1").value;        // "2"
typeof document.getElementById("val1").value; // "string"
parseFloat("2") + parseFloat("3");           // 5
"2" + "3";                                   // "23": concatenation
parseFloat("2.5");                           // 2.5
parseFloat("");                              // NaN
```

`parseFloat` parses a floating-point number from a string; it does not guarantee
valid input. A blank field produces `NaN`, meaning “Not a Number.” The comment
above the helper should be explained as conversion followed by validation,
rather than a promise that conversion prevents `NaN`.

For a number input, `.valueAsNumber` is another way to obtain a number directly;
it also returns `NaN` for an empty/unconvertible value.

Connect to the commented-out line 16: reading a value at page load would capture
the value at that moment, usually an empty string. Calling the helper inside a
click callback reads the user's current entries on every click.

## Line 31: Read the validation logic aloud

```javascript
if (!isNaN(num1) && !isNaN(num2)) {
```

- `isNaN(num1)` asks whether the parsed value is `NaN`.
- `!` negates that Boolean: `!isNaN(num1)` means the value is not `NaN`.
- `&&` requires both checks to be true. It short-circuits: if the first check is
  false, JavaScript does not evaluate the second check.

>“If the first parsed value is not NaN **and** the second parsed value is
not NaN, return the pair.” This checks for `NaN`; it does not enforce every
possible arithmetic rule, such as a nonzero divisor or integer endpoints.

**Question:** What happens if just one field is blank?\
**Answer:** Its parsed value is `NaN`, so the condition is false and
execution enters the `else` branch.

## Line 32: Return two values packaged in one array

```javascript
return [num1, num2];
```

>“A function returns one value. Here we package the two numbers in an array
and return that array as one composite value.” The square brackets form an
**array literal**. Position 0 holds the first number; position 1 holds the second.

## Lines 34–36: Why the `else`?

The failure branch displays `"Invalid input"` and returns `undefined`, signaling
to the caller that it did not get a usable pair. This is why callers check the
return value before using array indices.

**Ask:** Is the `else` keyword necessary here?\
**Expected answer:** No. Because `return` exits the function immediately on
success, the failure statements could follow the `if` without an `else`.
The existing `else` makes the success/failure alternatives explicit.

An explicit `return undefined` documents the failure signal. Reaching the end
of a function without a return value would also return `undefined`.

## Lines 40–49: Addition first in one function, then with a helper

Teach these as two successive versions. **The current file contains both in the
same function**, so a click executes both and writes the answer twice; the
second assignment replaces the first. In a live refactor, replace the first
version with the second instead of retaining both.

**First version, lines 41–44: do the work directly in `add`.**

```javascript
function add() {
    let num1 = parseFloat(document.getElementById("val1").value);
    let num2 = parseFloat(document.getElementById("val2").value);
    let result = num1 + num2;
    output.innerHTML = result;
}
```

Trace: read → convert → add → display. Try `2` and `3`, then clear one input.
The first case gives `5`; the blank-input case displays `NaN` because this
version has no validation branch.

**Second version, lines 48–49: delegate reading and validation to a helper.**

```javascript
function add() {
    let nums = getOperators();
    output.innerHTML = nums != undefined ? nums[0] + nums[1] : 'say wha..?';
}
```

`getOperators()` handles the shared input work; `add()` still chooses the
arithmetic and displays the result. Subtraction, multiplication, and division
reuse the helper instead of duplicating the input-reading and validation code.

Line 49 uses the **conditional (ternary) operator**:
`condition ? valueIfTrue : valueIfFalse`. Only the selected expression is
evaluated, so invalid input does not attempt to access `nums[0]`.
The existing `!= undefined` also excludes `null`; this helper returns an array
or `undefined`, so a strict `!== undefined` would work here too.

## Lines 48–49: Receiving, indexing, and destructuring the array

Line 48 assigns the returned array (or `undefined`) to `nums`. It does not unpack
it. Line 49 uses **array indexing**, also called **element access**, to read
`nums[0]` and `nums[1]`.

For explicit “unpacking” into separate variables, the JavaScript term is
**array destructuring**. Show this equivalent helper-based version:

```javascript
function add() {
    const nums = getOperators();
    if (nums === undefined) {
        output.innerHTML = 'say wha..?';
        return;
    }
    const [num1, num2] = nums;
    output.innerHTML = num1 + num2;
}
```

The destructuring declaration binds the first and second elements to `num1`
and `num2`. Check for failure first: destructuring `undefined` would throw an
error. See [MDN: destructuring](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Destructuring).

## Trace one complete click in the debugger

Set a breakpoint at line 48, enter `2` and `3`, and click `+`. Remember that the
first version of the calculation has already executed before this breakpoint.

1. **Step into** `getOperators()` to follow the call into the helper.
2. **Step over** the two conversions and inspect `num1` and `num2`.
3. Follow the condition and the return of `[2, 3]`.
4. Back in `add()`, inspect `nums`, then step over the output assignment.
5. Repeat with an empty input to follow the failure branch and fallback text.

**Ask:** Where does execution resume after the helper returns?\
**Expected answer:** At the call site in `add()`. The returned value is assigned
to `nums`, and execution continues to the next statement.

## Next demonstration: Add a summation button

Proposed demo behavior: sum every integer from `val1` through `val2`, including
both endpoints. Use whole-number inputs with the first no greater than the
second. For example, `2` through `5` means `2 + 3 + 4 + 5 = 14`, not just `2 + 5`.
These are demonstration choices, not new assignment requirements.

Show the HTML addition beside the existing operation buttons:

```html
<button class="op_button" id="sum" type="button"
        aria-label="Sum integers from first value through second value">&Sigma;</button>
```

Then show the same wiring pattern in JavaScript: select the button, register a
callback, read the operands through the existing helper, compute, and display.
The proposed code uses `textContent` because the messages and answer are text.

```javascript
const sum_btn = document.getElementById("sum");
sum_btn.addEventListener("click", summation);

function summation() {
    const nums = getOperators();
    if (nums === undefined) {
        output.textContent = '?';
        return;
    }

    const [start, end] = nums;
    if (!Number.isSafeInteger(start) || !Number.isSafeInteger(end)
            || start > end) {
        msg.textContent = 'Enter whole numbers with the first no greater than the second.';
        output.textContent = '?';
        return;
    }

    let total = 0;
    for (let value = start; value <= end; value++) {
        total += value;
    }

    msg.textContent = '';
    output.textContent = total;
}
```

Explain the extra validation: `getOperators()` checks for `NaN`; this operation
also needs integer endpoints in ascending order. `Number.isSafeInteger` excludes
fractions, infinities, and integers outside JavaScript's exact safe range.
Use small ranges for the live demonstration: a long loop blocks the page, and
even safe endpoints do not guarantee that a very large total is exact.

Step through `2` to `5`:

| `value` added | `total` afterward |
| --- | --- |
| 2 | 2 |
| 3 | 5 |
| 4 | 9 |
| 5 | 14 |

**Question:** Why start `total` at `0`, and why use `<=`?\
**Answer:** Zero is the starting value for an additive accumulator;
`<=` includes the upper endpoint. Starting at `1` adds an unwanted extra one,
and `<` omits the final number.

If useful, repeat the refactoring idea: move just the loop into a computation
helper, and replace the accumulator/loop block in `summation()` with
`const total = sumRange(start, end);`. Keep input validation in the callback.

```javascript
function sumRange(start, end) {
    let total = 0;
    for (let value = start; value <= end; value++) {
        total += value;
    }
    return total;
}
```

Now `summation()` coordinates the DOM work, while `sumRange()` takes numbers
and returns a number without touching the page.

Check `2` to `5` → `14`, `3` to `3` → `3`, and `-2` to `2` → `0`. A blank
input should use the existing invalid-input path; a fraction or reversed range
should show the summation-specific message. After an error, a valid sum should
clear the old message.
