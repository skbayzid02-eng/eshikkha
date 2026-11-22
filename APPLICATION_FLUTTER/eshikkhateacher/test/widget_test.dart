import 'package:flutter_test/flutter_test.dart';
import 'package:eshikkhateacher/main.dart';

void main() {
  testWidgets('Teacher app smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const EShikkhaTeacherApp());
    expect(find.text('ই-শিক্ষা শিক্ষক অ্যাপ'), findsOneWidget);
  });
}
